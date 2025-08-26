<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::query();

        if ($q = trim((string) $request->input('q', ''))) {
            $query->where(function ($qq) use ($q) {
                $qq->where('titulo', 'like', "%$q%")
                   ->orWhere('subtitulo', 'like', "%$q%")
                   ->orWhere('descricao', 'like', "%$q%")
                   ->orWhere('slug', 'like', "%$q%")
                   ->orWhere('url_amigavel', 'like', "%$q%");
            });
        }

        if ($request->has('ativo')) {
            $query->where('ativo', (bool) $request->boolean('ativo'));
        }
        if ($request->has('show_on_home')) {
            $query->where('show_on_home', (bool) $request->boolean('show_on_home'));
        }
        if ($slug = $request->input('slug')) {
            $query->where('slug', $slug);
        }
        if ($template = $request->input('template')) {
            $query->where('template', $template);
        }

        $query->orderBy('ordem', 'asc')->orderBy('id', 'desc');

        $perPage = (int) $request->input('per_page', 15);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:100|unique:sections,slug',
            'titulo' => 'required|string|max:255',
            'subtitulo' => 'sometimes|nullable|string|max:255',
            'descricao' => 'sometimes|nullable|string',
            'url_link' => 'sometimes|nullable|string|max:500',
            'texto_url' => 'sometimes|nullable|string|max:255',
            'botao' => 'sometimes|boolean',
            'url_amigavel' => 'sometimes|nullable|string|max:255|unique:sections,url_amigavel',
            'template' => 'required|in:destaques,sobre,servicos,blog',
            'show_on_home' => 'sometimes|boolean',
            'ordem' => 'sometimes|integer',
            'ativo' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $section = Section::create($validator->validated());
        return response()->json($section, 201);
    }

    public function show($sectionId)
    {
        $section = Section::with(['photos', 'items.photos', 'keywords'])->findOrFail($sectionId);
        return response()->json($section);
    }

    public function update(Request $request, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $validator = Validator::make($request->all(), [
            'slug' => 'sometimes|string|max:100|unique:sections,slug,' . $section->id,
            'titulo' => 'sometimes|string|max:255',
            'subtitulo' => 'sometimes|nullable|string|max:255',
            'descricao' => 'sometimes|nullable|string',
            'url_link' => 'sometimes|nullable|string|max:500',
            'texto_url' => 'sometimes|nullable|string|max:255',
            'botao' => 'sometimes|boolean',
            'url_amigavel' => 'sometimes|nullable|string|max:255|unique:sections,url_amigavel,' . $section->id,
            'template' => 'sometimes|in:destaques,sobre,servicos,blog',
            'show_on_home' => 'sometimes|boolean',
            'ordem' => 'sometimes|integer',
            'ativo' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }

        $section->fill($validator->validated());
        $section->save();

        return response()->json($section->fresh(['photos', 'items.photos', 'keywords']));
    }

    public function destroy($sectionId)
    {
        DB::beginTransaction();
        try {
            $section = Section::findOrFail($sectionId);
            $section->delete();
            DB::commit();
            return response()->json(['message' => 'Seção excluída com sucesso']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao excluir seção', 'error' => $e->getMessage()], 500);
        }
    }
}
