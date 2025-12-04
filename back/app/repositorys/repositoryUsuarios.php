<?php
class repositoryUsuarios
{

    private $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }



    public function delete($userid)
    {
        try {
            return $this->db->delete('users', ['id' => $userid]);
        } catch (Exception $e) {
            error_log("Erro no delete do repositoryUsersDelete: " . $e->getMessage());
            return false;
        }
    }


    public function create(Users $user)
    {
        $data = [
            'nome' => $user->getNome(),
            'telefone' => $user->getTelefone(),
            'senha' => $user->getSenha(),
            'setor' => $user->getSetor() //criar uma rolagem de nomes e enviar de la pra evitar conflitos em digitaçao direta
            //id gerado auto
        ];

        return $this->db->insert('users', $data);
    }




    public function findAll()
    {
        try {
            $results = $this->db->select('users');
            if (empty($results)) {
                return [];
            }

            $users = [];
            foreach ($results as $data) {
                $userObj = new Users($data);
                $users[] = $userObj->toArray();
            }
            return $users;
        } catch (Exception $e) {
            error_log("Erro no findAllUsers: " . $e->getMessage());
            return [];
        }
    }




    public function updateUserAdmin($id, array $data): bool
    {
        try {
            $camposPermitidos = ['nome', 'telefone', 'senha', 'setor'];
            $dadosFiltrados = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $camposPermitidos)) {
                    $dadosFiltrados[$key] = $value;
                }
            }
            $conditions = ['id' => $id];
            $result = $this->db->update('users', $dadosFiltrados, $conditions);
            return $result;
        } catch (Exception $e) {
            error_log("Erro no repositoryUpdateUser: " . $e->getMessage());
            return false;
        }
    }



    public function updateUserPopUp(string $id, array $data): bool
    {
        try {
            $dadosFiltrados = [];

            // Mapear os campos do frontend para o banco
            if (isset($data['telefone_user'])) {
                $dadosFiltrados['telefone'] = $data['telefone_user'];
            }

            if (isset($data['senha_user'])) {
                $dadosFiltrados['senha'] = password_hash(trim($data['senha_user']), PASSWORD_DEFAULT);
            }

            if (empty($dadosFiltrados)) {
                error_log("Nenhum dado válido enviado para updateUserPopUp.");
                return false;
            }

            $conditions = ['id' => $id];

            // Aqui você chama o update do seu Database class
            $result = $this->db->update('users', $dadosFiltrados, $conditions);

            return $result;
        } catch (Exception $e) {
            error_log("Erro no repositoryUpdateUserPopUp: " . $e->getMessage());
            return false;
        }
    }


    //metodo de filtrar usuarios por nome - id - telefone - setor, na interface de ediçao de usuarios
    public function filtroUserNINSadm($valor)
    {
        try {
            $results = $this->db->select('users');

            if (empty($results)) {
                return [];
            }

            $users = [];
            foreach ($results as $data) {
                if (
                    (isset($data['id']) && $data['id'] == $valor) ||
                    (isset($data['nome']) && stripos($data['nome'], $valor) !== false) ||
                    (isset($data['telefone']) && stripos($data['telefone'], $valor) !== false) ||
                    (isset($data['setor']) && stripos($data['setor'], $valor) !== false)
                ) {
                    $users[] = [
                        'id' => $data['id'],
                        'nome' => $data['nome'],
                        'telefone' => $data['telefone'],
                        'setor' => $data['setor'],
                    ];
                }
            }

            return $users;
        } catch (Exception $e) {
            error_log("Erro no filtroUserNINSadm: " . $e->getMessage());
            return [];
        }
    }



    public function loginName($nome)
    {
        try {
            $nome = trim((string)$nome);

            $results = $this->db->select('users', ['nome' => $nome], 1);

            if (empty($results)) {
                return [];
            }

            return $results[0]; // retorna user único

        } catch (Exception $e) {
            error_log("Erro em loginName: " . $e->getMessage());
            return [];
        }
    }



    public function filtroUserId($nome)
    {
        try {
            $nome = trim((string)$nome);

            // Usa apenas o nome como condição
            $results = $this->db->select('users', ['nome' => $nome], 1);

            if (empty($results)) {
                return null;
            }

            return [
                'id' => $results[0]['id']  // aqui pega a coluna 'id' do DB
            ];
        } catch (Exception $e) {
            error_log("Erro no filtroUserId: " . $e->getMessage());
            return null;
        }
    }


    public function filtroUserComumID($id)
    {
        try {
            $id = $id;
            $results = $this->db->select('users', ['id' => $id], 1);
            return !empty($results);
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
}
