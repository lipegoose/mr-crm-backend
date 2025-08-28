<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionKeywordController extends Controller
{
    public function index($sectionId)
    {
        $section = Section::with(['keywords' => function ($q) {
            $q->orderBy('nome', 'asc');
        }])->findOrFail($sectionId);

        return response()->json($section->keywords);
    }

    public function attach(Request $request, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $validator = Validator::make($request->all(), [
            'keyword_id' => 'required|integer|exists:keywords,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }
        $keywordId = (int) $validator->validated()['keyword_id'];

        // Evita duplicidade no pivot
        $section->keywords()->syncWithoutDetaching([$keywordId]);

        // Opcionalmente retornar a seção com keywords atualizadas
        $section->load('keywords');
        return response()->json([
            'message' => 'Keyword vinculada à seção com sucesso',
            'keywords' => $section->keywords,
        ], 200);
    }

    public function detach($sectionId, $keywordId)
    {
        $section = Section::findOrFail($sectionId);
        $keyword = Keyword::findOrFail($keywordId);

        $section->keywords()->detach($keyword->id);
        return response()->json(['message' => 'Keyword desvinculada da seção com sucesso'], 200);
    }
}
