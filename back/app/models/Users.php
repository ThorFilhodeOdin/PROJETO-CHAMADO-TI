<?php
class Users {
    private $id;
    private $nome;
    private $telefone;
    private $senha;
    private $setor;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'];
            $this->nome = $data['nome'] ?? '';
            $this->telefone = $data['telefone'] ?? '';
            $this->senha = $data['senha'] ?? '';
            $this->setor = $data['setor'] ?? '';
        }
    }

    public function toArray() {
        return [
            'id_user' => $this->id,
            'nome' => $this->nome,
            'telefone' => $this->telefone,
            'setor' => $this->setor
        ];
    }

//getters e setters nao sao obrigatorios mais caiem em boa pratica e deixar certas informaçoes dentro da propria classe,
// assim como no repository quando geramum objeto  e querem mudar lago, inves de puxar direto o nome, gera um get sendo mais seguro

    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function getSetor() {
        return $this->setor;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function setSetor($setor) {
        $this->setor = $setor;
    }

    public function __toString() {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
