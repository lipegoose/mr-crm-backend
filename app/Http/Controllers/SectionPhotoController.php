<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\SectionPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SectionPhotoController extends Controller
{
    public function index($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $photos = $section->photos()->orderBy('principal', 'desc')->orderBy('ordem', 'asc')->get();
        return response()->json($photos);
    }

    public function upload(Request $request, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $uploaded = [];
        $existingCount = SectionPhoto::where('section_id', $section->id)->count();
        $limit = 12; // Pode ser parametrizável

        foreach ($request->file('files') as $file) {
            if (($existingCount + count($uploaded)) >= $limit) {
                break; // respeitar limite
            }

            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $destDir = app()->basePath('public/sections/' . $section->id . '/fotos');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $filename);
            $relativePath = 'sections/' . $section->id . '/fotos/' . $filename;

            $lastOrder = SectionPhoto::where('section_id', $section->id)->max('ordem') ?? 0;

            $photo = new SectionPhoto();
            $photo->section_id = $section->id;
            $photo->path = $relativePath;
            $photo->ordem = $lastOrder + 1;
            // Se for a primeira foto da seção, marcar como principal
            $hasAny = SectionPhoto::where('section_id', $section->id)->exists();
            $photo->principal = $hasAny ? false : true;
            $photo->save();

            $uploaded[] = $photo;
        }

        return response()->json(['uploaded' => $uploaded], 201);
    }

    public function update(Request $request, $sectionId, $photoId)
    {
        $photo = SectionPhoto::where('section_id', $sectionId)->findOrFail($photoId);

        $validator = Validator::make($request->all(), [
            'principal' => 'sometimes|boolean',
            'alt_text' => 'sometimes|nullable|string|max:255',
            'ordem' => 'sometimes|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        foreach ($data as $k => $v) {
            $photo->$k = $v;
        }
        $photo->save();

        return response()->json($photo->fresh());
    }

    public function delete($sectionId, $photoId)
    {
        $photo = SectionPhoto::where('section_id', $sectionId)->findOrFail($photoId);
        $photo->delete();
        return response()->json(['message' => 'Foto removida']);
    }
}
