<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class PublicSectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::with([
            'photos' => function ($q) {
                $q->orderBy('principal', 'desc')->orderBy('ordem', 'asc');
            },
            'items' => function ($q) {
                $q->where('ativo', true)->orderBy('ordem', 'asc');
            },
            'items.photos' => function ($q) {
                $q->orderBy('principal', 'desc')->orderBy('ordem', 'asc');
            },
            'items.keywords',
            'keywords'
        ])
        ->whereNotNull('published_at')
        ->where('ativo', true)
        ->orderBy('ordem', 'asc');

        if ($request->boolean('home')) {
            $query->where('show_on_home', true);
        }
        if ($slug = $request->input('slug')) {
            $query->where('slug', $slug);
        }
        if ($template = $request->input('template')) {
            $query->where('template', $template);
        }
        if ($keyword = $request->input('keyword')) {
            $query->whereHas('items.keywords', function ($q) use ($keyword) {
                $q->where('slug', $keyword)->orWhere('nome', 'like', "%$keyword%");
            });
        }

        $sections = $query->get();

        // Transformar no formato público esperado
        $data = [
            'sections' => $sections->map(function ($section) {
                return [
                    'slug' => $section->slug,
                    'titulo' => $section->titulo,
                    'subtitulo' => $section->subtitulo,
                    'descricao' => $section->descricao,
                    'url_link' => $section->url_link,
                    'texto_url' => $section->texto_url,
                    'botao' => (bool) $section->botao,
                    'url_amigavel' => $section->url_amigavel,
                    'template' => $section->template,
                    'fotos' => $section->photos->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'url' => $p->url,
                            'principal' => (bool) $p->principal,
                            'alt_text' => $p->alt_text,
                        ];
                    })->values(),
                    'items' => $section->items->map(function ($item) {
                        $principal = $item->photos->firstWhere('principal', true) ?? $item->photos->first();
                        return [
                            'id' => $item->id,
                            'titulo' => $item->titulo,
                            'subtitulo' => $item->subtitulo,
                            'descricao' => $item->descricao,
                            'url_link' => $item->url_link,
                            'texto_url' => $item->texto_url,
                            'botao' => (bool) $item->botao,
                            'url_amigavel' => $item->url_amigavel,
                            'foto_principal' => $principal ? [
                                'url' => $principal->url,
                                'alt_text' => $principal->alt_text,
                            ] : null,
                            'galeria' => $item->photos->map(function ($p) {
                                return [
                                    'id' => $p->id,
                                    'url' => $p->url,
                                    'alt_text' => $p->alt_text,
                                ];
                            })->values(),
                            'keywords' => $item->keywords->map(function ($k) {
                                return [ 'id' => $k->id, 'nome' => $k->nome, 'slug' => $k->slug ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values()
        ];

        return response()->json($data);
    }
}
