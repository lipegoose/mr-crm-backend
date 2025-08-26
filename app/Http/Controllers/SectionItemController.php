<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\SectionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionItemController extends Controller
{
    public function index($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $items = $section->items()->with('photos')->orderBy('ordem','asc')->get();
        return response()->json($items);
    }

    public function store(Request $request, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'subtitulo' => 'sometimes|nullable|string|max:255',
            'descricao' => 'sometimes|nullable|string',
            'url_link' => 'sometimes|nullable|url',
            'texto_url' => 'sometimes|nullable|string|max:255',
            'botao' => 'sometimes|boolean',
            'url_amigavel' => 'sometimes|nullable|string|max:255',
            'ordem' => 'sometimes|integer',
            'ativo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['section_id'] = $section->id;

        $item = SectionItem::create($data);
        return response()->json($item->load('photos'), 201);
    }

    public function show($sectionId, $itemId)
    {
        $item = SectionItem::where('section_id', $sectionId)->with(['photos','keywords'])->findOrFail($itemId);
        return response()->json($item);
    }

    public function update(Request $request, $sectionId, $itemId)
    {
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|string|max:255',
            'subtitulo' => 'sometimes|nullable|string|max:255',
            'descricao' => 'sometimes|nullable|string',
            'url_link' => 'sometimes|nullable|url',
            'texto_url' => 'sometimes|nullable|string|max:255',
            'botao' => 'sometimes|boolean',
            'url_amigavel' => 'sometimes|nullable|string|max:255',
            'ordem' => 'sometimes|integer',
            'ativo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $item->fill($validator->validated());
        $item->save();
        return response()->json($item->fresh(['photos','keywords']));
    }

    public function destroy($sectionId, $itemId)
    {
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);
        $item->delete();
        return response()->json(['message' => 'Item excluído']);
    }
}
