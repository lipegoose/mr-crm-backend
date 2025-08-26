<?php

namespace App\Http\Controllers;

use App\Models\SectionItem;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionItemKeywordController extends Controller
{
    public function attach(Request $request, $sectionId, $itemId)
    {
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'keyword_ids' => 'required|array',
            'keyword_ids.*' => 'integer|exists:keywords,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $ids = array_values(array_unique($request->keyword_ids));
        $item->keywords()->syncWithoutDetaching($ids);

        return response()->json([
            'message' => 'Keywords vinculadas',
            'keywords' => $item->keywords()->get()
        ]);
    }

    public function detach($sectionId, $itemId, $keywordId)
    {
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);
        $keyword = Keyword::findOrFail($keywordId);

        $item->keywords()->detach($keyword->id);

        return response()->json([
            'message' => 'Keyword desvinculada',
            'keywords' => $item->keywords()->get()
        ]);
    }
}
