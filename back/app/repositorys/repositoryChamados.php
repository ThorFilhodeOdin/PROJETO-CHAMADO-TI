<?php
class repositoryChamados
{

    private $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }


    public function delete($chamadoid)
    {
        try {
            return $this->db->delete('chamados', ['id_chamado' => $chamadoid]);
        } catch (Exception $e) {
            error_log("Erro no delete do repositoryChamados: " . $e->getMessage());
            return false;
        }
    }



    public function filtroUserComumId($id)
    {
        try {
            $results = $this->db->select('users', ['id' => $id], 1);
            return !empty($results) ? $results[0] : false;
        } catch (Exception $e) {
            error_log("Erro no filtroUserComumId: " . $e->getMessage());
            return false;
        }
    }




    public function filtroUserAdminId($id)
    {
        try {

            $results = $this->db->select('users', [
                'id' => $id, // O array de condições que será traduzido para o WHERE
                'setor' => 'TI'
            ]);

            return !empty($results);
        } catch (Exception $e) {
            error_log("❌ Erro no filtroUserAdminId: " . $e->getMessage());
            return false;
        }
    }


    public function getMeusChamados($idUser)
    {
        try {

            $userData = $this->db->select('users', ['id' => $idUser]);

            if (empty($userData)) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'Usuário não encontrado'
                ];
            }

            $nomeUser = $userData[0]['nome'];

            $chamadoConditions = [
                'nome' => $nomeUser,
                'status' => 'aberto'
            ];

            $results = $this->db->select('chamados', $chamadoConditions);

            foreach ($results as &$c) {

                for ($i = 1; $i <= 6; $i++) {
                    $campo = "imagem$i";

                    if (!empty($c[$campo])) {
                        $c[$campo] = "data:image/jpeg;base64," . base64_encode($c[$campo]);
                    } else {
                        $c[$campo] = null;
                    }
                }
            }
            if (empty($results)) {
                return [
                    'success' => true,
                    'data' => [],
                    'message' => 'Nenhum chamado aberto encontrado para ' . $nomeUser
                ];
            }

            $chamados = array_map(function ($chamado) {
                unset($chamado['status']);
                return $chamado;
            }, $results);

            return [
                'success' => true,
                'data' => $chamados,
                'message' => 'Chamados abertos carregados com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro no getMeusChamados: " . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro interno ao carregar chamados'
            ];
        }
    }






    public function create(Chamados $chamado)
    {
        $data = [
            'nome' => $chamado->getNome(),
            'numero' => $chamado->getNumero(),
            'tipo' => $chamado->getTipo(),
            'status' => $chamado->getStatus() ?? 'aberto',
            'data' => $chamado->getData() ?? date('Y-m-d'),
            'imagem1' => $chamado->getImagem1(),
            'imagem2' => $chamado->getImagem2(),
            'imagem3' => $chamado->getImagem3(),
            'imagem4' => $chamado->getImagem4(),
            'imagem5' => $chamado->getImagem5(),
            'imagem6' => $chamado->getImagem6(),
            'descricao' => $chamado->getDescricao(),
            'setor' => $chamado->getSetor()
        ];

        return $this->db->insert('chamados', $data);
    }







    public function updateChamadoComum($idChamado, $data): bool
    {

        try {

            $camposPermitidos = [
                'tipo',
                'descricao',
                'imagem1',
                'imagem2',
                'imagem3',
                'imagem4',
                'imagem5',
                'imagem6',
                'status'
            ];

            $dadosFiltrados = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $camposPermitidos, true)) {
                    if (strpos($key, 'imagem') === 0 && is_string($value)) {
                        if (preg_match('#^data:image/\w+;base64,#i', $value)) {
                            $value = base64_decode(
                                preg_replace('#^data:image/\w+;base64,#i', '', $value)
                            );
                        }
                    }
                    $dadosFiltrados[$key] = $value;
                }
            }

            if (empty($dadosFiltrados)) {
                error_log("Nenhum campo válido para atualizar no chamado #{$idChamado}");
                return false;
            }

            $conditions = ['id_chamado' => $idChamado];

            $result = $this->db->update('chamados', $dadosFiltrados, $conditions);



            return $result;
        } catch (Exception $e) {
            error_log("Erro no updateChamado do repository: " . $e->getMessage());
            return false;
        }
    }



    public function updateChamadoAbertoAdmin($idChamado, $data): bool
    {
        error_log("OQ CHEGA NO REPOSITORY" . $data);
        try {
            $dadosFiltrados = [];
            $camposPermitidos = [
                'tipo',
                'descricao',
                'imagem1',
                'imagem2',
                'imagem3',
                'imagem4',
                'imagem5',
                'imagem6',
                'status',
                'nome',
                'numero',
                'data',
                'setor'
            ];

            $dadosFiltrados = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $camposPermitidos, true)) {
                    if (strpos($key, 'imagem') === 0 && is_string($value)) {
                        if (preg_match('#^data:image/\w+;base64,#i', $value)) {
                            $value = base64_decode(
                                preg_replace('#^data:image/\w+;base64,#i', '', $value)
                            );
                        }
                    }
                    $dadosFiltrados[$key] = $value;
                }
            }

            if (empty($dadosFiltrados)) {
                error_log("Nenhum campo válido para atualizar no chamado #{$idChamado}");
                return false;
            }

            $conditions = ['id_chamado' => $idChamado];

            $result = $this->db->update('chamados', $dadosFiltrados, $conditions);



            return $result;
        } catch (Exception $e) {
            error_log("Erro no updateChamado do repository: " . $e->getMessage());
            return false;
        }
    }




    public function findAll()
    {
        try {
            $results = $this->db->select('chamados');

            if (empty($results)) {
                return [];
            }

            $chamados = [];
            foreach ($results as $data) {

                unset($data['imagem1'], $data['imagem2'], $data['imagem3'], $data['imagem4'], $data['imagem5'], $data['imagem6']);

                $chamados[] = $data;
            }

            return $chamados;
        } catch (Exception $e) {
            error_log("Erro no findAllChamados: " . $e->getMessage());
            return [];
        }
    }



    public function findAllTi()
    {
        try {
            $results = $this->db->select('chamados', [
                'setor' => 'TI' // Condição simples: coluna 'setor' deve ser 'TI'
            ]);
            if (empty($results)) {
                return [];
            }
            $chamados = [];
            foreach ($results as $data) {
                unset(
                    $data['imagem1'],
                    $data['imagem2'],
                    $data['imagem3'],
                    $data['imagem4'],
                    $data['imagem5'],
                    $data['imagem6']
                );
                $chamados[] = $data;
            }
            return $chamados;
        } catch (Exception $e) {
            error_log("Erro no findAllTi: " . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id_chamado): ?array
    {
        try {
            $results = $this->db->select('chamados', ['id_chamado' => $id_chamado], 1); // LIMIT 1

            if (empty($results)) {
                return null; // não encontrou
            }

            return $results[0]; // retorna apenas o registro encontrado
        } catch (Exception $e) {
            error_log("Erro no findByIdChamado: " . $e->getMessage());
            return null;
        }
    }







    public function filtroUserNINSTadm($valor)
    {
        try {

            $valor = trim(strip_tags($valor));
            if ($valor === '') {
                return [];
            }

            $results = $this->db->select('chamados');

            if (empty($results)) {
                return [];
            }

            $chamados = [];
            foreach ($results as $data) {
                if (
                    (isset($data['id_chamado']) && $data['id_chamado'] == $valor) ||
                    (isset($data['nome']) && stripos($data['nome'], $valor) !== false) ||
                    (isset($data['telefone']) && stripos($data['telefone'], $valor) !== false) ||
                    (isset($data['status']) && stripos($data['status'], $valor) !== false) ||
                    (isset($data['tipo']) && stripos($data['tipo'], $valor) !== false) ||
                    (isset($data['setor']) && stripos($data['setor'], $valor) !== false)
                ) {
                    $chamados[] = [
                        'id_chamado' => $data['id_chamado'] ?? null,
                        'nome'       => $data['nome'] ?? '',
                        'telefone'   => $data['telefone'] ?? '',
                        'setor'      => $data['setor'] ?? '',
                        'tipo'       => $data['tipo'] ?? '',
                        'status'     => $data['status'] ?? '',
                        'data'       => $data['data'] ?? '',
                    ];
                }
            }

            return $chamados;
        } catch (Exception $e) {
            error_log("Erro no filtroUserNINSadm: " . $e->getMessage());
            return [];
        }
    }





    public function filtroChamadoId($id)
    {
        try {
            $id = (int) $id;
            if ($id <= 0) {
                return null;
            }

            $results = $this->db->select('chamados', ['id_chamado' => $id]);


            if (empty($results)) {
                return null;
            }

            return $results[0];
        } catch (Exception $e) {
            error_log("Erro no filtroChamadoId: " . $e->getMessage());
            return null;
        }
    }

    public function finalizarChamadoAdmin($idChamado)
    {
        try {
            $idChamado = (int) $idChamado;
            return $this->db->update('chamados', ['status' => 'finalizado'], ['id_chamado' => $idChamado]);
        } catch (Exception $e) {
            error_log("Erro ao finalizar chamado: " . $e->getMessage());
            return false;
        }
    }

    public function reabrirChamadoAdmin($idChamado)
    {
        try {
            $idChamado = (int) $idChamado;
            return $this->db->update('chamados', ['status' => 'aberto'], ['id_chamado' => $idChamado]);
        } catch (Exception $e) {
            error_log("Erro ao reabrir chamado: " . $e->getMessage());
            return false;
        }
    }
}
