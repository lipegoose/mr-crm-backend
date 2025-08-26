<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KeywordController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $query = Keyword::query();
        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('nome', 'like', "%$q%")
                   ->orWhere('slug', 'like', "%$q%")
                   ->orWhere('descricao', 'like', "%$q%");
            });
        }
        $perPage = (int) $request->input('per_page', 20);
        return response()->json($query->orderBy('nome')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:100|unique:keywords,nome',
            'slug' => 'sometimes|nullable|string|max:120|unique:keywords,slug',
            'descricao' => 'sometimes|nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nome']);
        }
        $keyword = Keyword::create($data);
        return response()->json($keyword, 201);
    }

    public function update(Request $request, $id)
    {
        $keyword = Keyword::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|string|max:100|unique:keywords,nome,' . $keyword->id,
            'slug' => 'sometimes|nullable|string|max:120|unique:keywords,slug,' . $keyword->id,
            'descricao' => 'sometimes|nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
        }
        $keyword->fill($validator->validated());
        $keyword->save();
        return response()->json($keyword);
    }

    public function destroy($id)
    {
        $keyword = Keyword::findOrFail($id);
        $keyword->delete();
        return response()->json(['message' => 'Keyword removida']);
    }
}
