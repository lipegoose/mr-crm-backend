<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImovelImagem extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'imoveis_imagens';

    /**
     * Atributos adicionados ao array/JSON do modelo.
     *
     * @var array
     */
    protected $appends = ['url'];

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'imovel_id',
        'titulo',
        'caminho',
        'ordem',
        'principal',
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
        'principal' => 'boolean',
    ];

    /**
     * Eventos do modelo
     */
    protected static function booted()
    {
        // Antes de criar, definir usuário que está criando
        static::creating(function ($imagem) {
            if (empty($imagem->created_by) && Auth::check()) {
                $imagem->created_by = Auth::id();
            }
        });
        
        // Antes de atualizar, definir usuário que está atualizando
        static::updating(function ($imagem) {
            if (Auth::check()) {
                $imagem->updated_by = Auth::id();
            }
        });
        
        // Quando uma imagem é definida como principal, remover o status de principal das outras
        static::saved(function ($imagem) {
            if ($imagem->principal) {
                self::where('imovel_id', $imagem->imovel_id)
                    ->where('id', '!=', $imagem->id)
                    ->update(['principal' => false]);
            }
        });
        
        // Quando uma imagem é excluída, remover o arquivo físico
        static::deleting(function ($imagem) {
            if ($imagem->caminho) {
                // Em Lumen, use app()->basePath('public/...') pois public_path() pode não estar disponível
                $absolute = app()->basePath('public/' . ltrim($imagem->caminho, '/'));
                if (file_exists($absolute)) {
                    @unlink($absolute);
                }
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
     * Gera a URL completa da imagem.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if (empty($this->caminho)) {
            return null;
        }
        // Arquivos são salvos em public/{caminho} e acessíveis diretamente
        return url('/' . ltrim($this->caminho, '/'));
    }
    
    /**
     * Define esta imagem como a principal do imóvel.
     *
     * @return bool
     */
    public function definirComoPrincipal()
    {
        $this->principal = true;
        return $this->save();
    }
    
    /**
     * Atualiza a ordem desta imagem.
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
    
    /**
     * Escopo: Apenas imagens principais.
     */
    public function scopePrincipal($query)
    {
        return $query->where('principal', true);
    }
}
