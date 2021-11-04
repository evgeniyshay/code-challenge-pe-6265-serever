<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class WordController extends Controller
{
    private const DICTIONARY_FILE = 'data/dictionary.txt';
    private const DICTIONARY_CACHE = 'dictionary';

    private static function readDictionary()
    {
        if (!Cache::has(self::DICTIONARY_CACHE)) {
            $content = Storage::get(self::DICTIONARY_FILE);

            $data = explode("\n", $content);
            $data_count = count($data);

            if ($data[$data_count - 1] === '') {
                array_pop($data);
            }

            Cache::put(self::DICTIONARY_CACHE, $data);
        } else {
            $data = Cache::get(self::DICTIONARY_CACHE);
        }

        return $data;
    }

    private static function updateDictionary($data)
    {
        Cache::put(self::DICTIONARY_CACHE, $data);

        $content = implode("\n", $data);

        Storage::put(self::DICTIONARY_FILE, $content);
    }

    public function index()
    {
        return self::readDictionary();
    }
 
    public function exists($word)
    {
        $dictionary = self::readDictionary();

        return in_array(urldecode($word), $dictionary);
    }

    public function add(Request $request)
    {
        $dictionary = self::readDictionary();

        $word = $request->input('word');
        $in_dictionary = in_array($word, $dictionary);

        if ($in_dictionary !== false) {
            return response()->json([
                'message' => 'The word is already in the dictionary.',
            ], 409);
        }

        array_push($dictionary, $word);

        self::updateDictionary($dictionary);

        return response()->json([
            'message' => 'The word was successfully added to the dictionary.',
        ], 201);
    }

    public function delete(Request $request, $word)
    {
        $dictionary = self::readDictionary();

        $key = array_search(urldecode($word), $dictionary);

        if ($key === false) {
            return response()->json([
                'message' => 'The are no such word in the dictionary.',
            ], 404);
        }

        unset($dictionary[$key]);

        self::updateDictionary($dictionary);

        return response()->json([
            'message' => 'The word was successfully removed from the dictionary.',
        ], 200);
    }
}
