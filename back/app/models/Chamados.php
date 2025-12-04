<?php
class Chamados
{
    private $id_chamado;
    private $nome;
    private $numero;
    private $tipo;
    private $status;
    private $data;
    private $descricao;
    private $setor;
    private $imagens = []; // até 6 imagens

    public function __construct(array $data = [])
    {
        $this->id_chamado = $data['id_chamado'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->numero = $data['numero'] ?? '';
        $this->tipo = $data['tipo'] ?? '';
        $this->status = $data['status'] ?? 'aberto';
        $this->data = $data['data'] ?? date('Y-m-d');
        $this->descricao = $data['descricao'] ?? '';
        $this->setor = $data['setor'] ?? '';
        
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($data["imagem$i"])) {
                $this->imagens["imagem$i"] = $data["imagem$i"];
            }
        }
    }

    public function toArray(): array
    {
        $data = [
            'id_chamado' => $this->id_chamado,
            'nome' => $this->nome,
            'numero' => $this->numero,
            'tipo' => $this->tipo,
            'status' => $this->status,
            'data' => $this->data,
            'descricao' => $this->descricao,
            'setor' => $this->setor
        ];
        
        foreach ($this->imagens as $key => $img) {
            $data[$key] = $img;
        }

        return $data;
    }

    public function getIdChamado() { return $this->id_chamado; }
    public function getNome() { return $this->nome; }
    public function getNumero() { return $this->numero; }
    public function getTipo() { return $this->tipo; }
    public function getStatus() { return $this->status; }
    public function getData() { return $this->data; }
    public function getDescricao() { return $this->descricao; }
    public function getSetor() { return $this->setor; }
    public function getImagem1() { return $this->imagens['imagem1'] ?? null; }
    public function getImagem2() { return $this->imagens['imagem2'] ?? null; }
    public function getImagem3() { return $this->imagens['imagem3'] ?? null; }
    public function getImagem4() { return $this->imagens['imagem4'] ?? null; }
    public function getImagem5() { return $this->imagens['imagem5'] ?? null; }
    public function getImagem6() { return $this->imagens['imagem6'] ?? null; }  
      
    public function setNome($nome) { $this->nome = $nome; }
    public function setNumero($numero) { $this->numero = $numero; }
    public function setTipo($tipo) { $this->tipo = $tipo; }
    public function setStatus($status) { $this->status = $status; }
    public function setData($data) { $this->data = $data; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setSetor($setor) { $this->setor = $setor; }

    public function addImagem($index, $imagem)
    {
        if ($index >= 1 && $index <= 6) {
            $this->imagens["imagem$index"] = $imagem;
        }
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
