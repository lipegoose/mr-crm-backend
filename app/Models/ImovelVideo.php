<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImovelVideo extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'imoveis_videos';

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'imovel_id',
        'titulo',
        'url',
        'ordem',
        'created_by',
        'updated_by',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'ordem' => 'integer',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($video) {
            if (empty($video->created_by) && Auth::check()) {
                $video->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($video) {
            if (Auth::check()) {
                $video->updated_by = Auth::id();
            }
        });
    }

    /**
     * Imóvel relacionado.
     */
    public function imovel()
    {
        return $this->belongsTo(Imovel::class);
    }

    /**
     * Usuário que criou o registro.
     */
    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que atualizou o registro.
     */
    public function atualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Extrai o ID do vídeo da URL (YouTube ou Vimeo).
     *
     * @return string|null
     */
    public function getVideoIdAttribute()
    {
        if (empty($this->url)) {
            return null;
        }
        
        // Extrair ID do YouTube
        if (strpos($this->url, 'youtube.com') !== false || strpos($this->url, 'youtu.be') !== false) {
            $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
            preg_match($pattern, $this->url, $matches);
            
            return isset($matches[1]) ? $matches[1] : null;
        }
        
        // Extrair ID do Vimeo
        if (strpos($this->url, 'vimeo.com') !== false) {
            $pattern = '/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|)(\d+)(?:$|\/|\?)/i';
            preg_match($pattern, $this->url, $matches);
            
            return isset($matches[1]) ? $matches[1] : null;
        }
        
        return null;
    }
    
    /**
     * Gera a URL da thumbnail do vídeo.
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute()
    {
        $videoId = $this->video_id;
        
        if (!$videoId) {
            return null;
        }
        
        // Thumbnail do YouTube
        if (strpos($this->url, 'youtube.com') !== false || strpos($this->url, 'youtu.be') !== false) {
            return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
        }
        
        // Thumbnail do Vimeo
        if (strpos($this->url, 'vimeo.com') !== false) {
            // Nota: Para Vimeo, idealmente seria necessário usar a API deles para obter a thumbnail
            // Esta é uma implementação simplificada
            return "https://vumbnail.com/{$videoId}.jpg";
        }
        
        return null;
    }
    
    /**
     * Gera a URL de incorporação do vídeo.
     *
     * @return string|null
     */
    public function getEmbedUrlAttribute()
    {
        $videoId = $this->video_id;
        
        if (!$videoId) {
            return null;
        }
        
        // URL de incorporação do YouTube
        if (strpos($this->url, 'youtube.com') !== false || strpos($this->url, 'youtu.be') !== false) {
            return "https://www.youtube.com/embed/{$videoId}";
        }
        
        // URL de incorporação do Vimeo
        if (strpos($this->url, 'vimeo.com') !== false) {
            return "https://player.vimeo.com/video/{$videoId}";
        }
        
        return null;
    }
    
    /**
     * Atualiza a ordem deste vídeo.
     *
     * @param int $ordem
     * @return bool
     */
    public function atualizarOrdem($ordem)
    {
        $this->ordem = $ordem;
        return $this->save();
    }
    
    /**
     * Escopo: Ordenar por ordem.
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem', 'asc');
    }
}
