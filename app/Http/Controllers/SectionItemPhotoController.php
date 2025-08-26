<?php

namespace App\Http\Controllers;

use App\Models\SectionItem;
use App\Models\SectionItemPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SectionItemPhotoController extends Controller
{
    public function index($sectionId, $itemId)
    {
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);
        $photos = $item->photos()->orderBy('principal', 'desc')->orderBy('ordem', 'asc')->get();
        return response()->json($photos);
    }

    public function upload(Request $request, $sectionId, $itemId)
    {
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $uploaded = [];
        $existingCount = SectionItemPhoto::where('section_item_id', $item->id)->count();
        $limit = 12; // parametrizável

        foreach ($request->file('files') as $file) {
            if (($existingCount + count($uploaded)) >= $limit) {
                break;
            }

            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $destDir = app()->basePath('public/sections/' . $item->section_id . '/items/' . $item->id . '/fotos');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $filename);
            $relativePath = 'sections/' . $item->section_id . '/items/' . $item->id . '/fotos/' . $filename;

            $lastOrder = SectionItemPhoto::where('section_item_id', $item->id)->max('ordem') ?? 0;

            $photo = new SectionItemPhoto();
            $photo->section_item_id = $item->id;
            $photo->path = $relativePath;
            $photo->ordem = $lastOrder + 1;
            $hasAny = SectionItemPhoto::where('section_item_id', $item->id)->exists();
            $photo->principal = $hasAny ? false : true;
            $photo->save();

            $uploaded[] = $photo;
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    public function update(Request $request, $sectionId, $itemId, $photoId)
    {
        // Garantir que o item pertence à seção
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);
        $photo = SectionItemPhoto::where('section_item_id', $item->id)->findOrFail($photoId);

        $validator = Validator::make($request->all(), [
            'principal' => 'sometimes|boolean',
            'alt_text' => 'sometimes|nullable|string|max:255',
            'ordem' => 'sometimes|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        foreach ($validator->validated() as $k => $v) {
            $photo->$k = $v;
        }
        $photo->save();

        return response()->json($photo->fresh());
    }

    public function delete($sectionId, $itemId, $photoId)
    {
        $item = SectionItem::where('section_id', $sectionId)->findOrFail($itemId);
        $photo = SectionItemPhoto::where('section_item_id', $item->id)->findOrFail($photoId);
        $photo->delete();
        return response()->json(['message' => 'Foto removida']);
    }
}
