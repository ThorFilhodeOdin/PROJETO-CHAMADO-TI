<?php
class UserService
{
    private $repositoryUsuarios;

    public function __construct()
    {
        $database = new Database();
        $this->repositoryUsuarios = new repositoryUsuarios($database);
    }



    private function validarAdmin($dadosUserRequest) //criar constante imutavel no front com um nome esquisito que carregara o id de quem logou na pg, e essa constante sera o dadosUserRequest
    {
        try {

            $id = trim(strip_tags($dadosUserRequest['id'] ?? ''));
            if (empty($id) || !preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
                return ['success' => false, 'message' => 'ID do usuário inválido aqui'];
            }
            $isAdmin = $this->repositoryUsuarios->filtroUserAdminId($id);
            if (!$isAdmin) {

                return ['success' => false, 'message' => 'Usuário sem permissão'];
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro na validação de admin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno ao validar usuário'];
        }
    }


    public function validarUserComumRequest($idUser)
    { //ID USER NAO É UM ARRAY
        {
            try {
                $id = trim(strip_tags($idUser)); //ID USER NAO É UM ARRAY
                if (empty($id) || !preg_match('/^[0-9a-fA-F-]{36}$/', $id)) { //caso tente acessar qualquer api diretamente sem id nao vai conseguir 
                    return ['success' => false, 'message' => 'ID do usuário inválido'];
                }

                $isValid = $this->repositoryUsuarios->filtroUserComumId($id);
                if (!$isValid) {
                    return ['success' => false, 'message' => 'Usuário invalido'];
                }
                return true;
            } catch (Exception $e) {
                error_log("Erro na validação de usuario: " . $e->getMessage());
                return ['success' => false, 'message' => 'Erro interno ao validar usuário'];
            }
        }
    }








    public function validaAdminExibicao($dadosUserRequest)
    {
        try {

            $id = trim(strip_tags($dadosUserRequest));

            if (empty($id) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
                return ['success' => false, 'message' => 'ID do usuário inválido'];
            }

            $isAdmin = $this->repositoryUsuarios->filtroUserAdminId($id);
            if (!$isAdmin) {
                return ['success' => false, 'message' => 'Usuário sem permissão'];
            }

            return ['success' => true, 'message' => 'Usuário autorizado'];
        } catch (Exception $e) {
            error_log("Erro na validação de admin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno ao validar usuário'];
        }
    }






    public function deleteUser($userid, $dadosUserRequest)
    {
        try {
            if (isset($dadosUserRequest['idUserSession'])) {
                $dadosUserRequest['id'] = $dadosUserRequest['idUserSession'];
            } else if (!isset($dadosUserRequest['id'])) {
                $dadosUserRequest['id'] = '';
            }
            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }
            $existingUser = $this->validarUserComumRequest($userid);
            if (!$existingUser) {
                return ['validaçao de existencia de id true' => false, 'message' => 'User não encontrado'];
            }
            $result = $this->repositoryUsuarios->delete($userid);
            return $result
                ? ['success' => true, 'User deletado com sucesso']
                : ['success' => false, 'Erro ao deletar User'];
        } catch (Exception $e) {
            error_log("Erro no deleteUserServiceAdmin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao deletar usuário'];
        }
    }



    public function getAllUsersAdmin($dadosUserRequest)
    {
        try {

            if (isset($dadosUserRequest['idUserSession'])) {
                $dadosUserRequest['id'] = $dadosUserRequest['idUserSession'];
            } else if (!isset($dadosUserRequest['id'])) {
                $dadosUserRequest['id'] = '';
            }
            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }

            $usersCompleto = $this->repositoryUsuarios->findAll();
            $usersFiltrados = array_map(function ($user) {
                return [
                    'id_user' => $user['id_user'] ?? null, // Aqui está a 'tradução'
                    'nome'    => $user['nome'] ?? null,
                    'setor'   => $user['setor'] ?? null,
                    'telefone'   => $user['telefone'] ?? null,
                ];
            }, $usersCompleto);

            return [
                'success' => true,
                'data' => $usersFiltrados, // Retorna os dados filtrados
                'total' => count($usersFiltrados),
                'message' => 'Users carregados com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar todos os Users: " . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'total' => 0,
                'message' => 'Erro ao carregar Users'
            ];
        }
    }



    public function getMyUser($myId) //nome, senha e numero = PUBLIC mas retorna algo pro front
    {
        try {

            $check = $this->validarUserComumRequest($myId);
            if ($check !== true) {
                return $check;
            }

            $myId = trim(strip_tags($myId));
            if (empty($myId) || !preg_match('/^[0-9a-fA-F-]{36}$/', $myId)) {
                return [
                    'success' => false,
                    'data' => [],
                    'total' => 0,
                    'message' => 'ID de usuário inválido'
                ];
            }

            $user = $this->repositoryUsuarios->filtroUserId($myId);
            unset($user['id']);
            unset($user['setor']);
            if (is_array($user)) {
                foreach ($user as $key => $value) {
                    if (is_string($value)) {
                        $user[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
                    }
                }
            }
            return [
                'success' => true,
                'data' => $user,
                'total' => count($user),
                'message' => 'User carregado com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar o User: " . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'total' => 0,
                'message' => 'Erro ao carregar User'
            ];
        }
    }




    public function createUser($userData, $dadosUserRequest)
    {
        try {
            if (isset($dadosUserRequest['idUserSession'])) {
                $dadosUserRequest['id'] = $dadosUserRequest['idUserSession'];
            } else if (!isset($dadosUserRequest['id'])) {
                $dadosUserRequest['id'] = '';
            }
            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }

            if (empty($userData['nome'])) {
                return ['success' => false, 'message' => 'Nome do User é obrigatório'];
            }

            if (empty($userData['senha'])) {
                return ['success' => false, 'message' => 'Senha do User é obrigatório'];
            }

            if (empty($userData['telefone'])) {
                return ['success' => false, 'message' => 'Telefone do User é obrigatório'];
            }

            if (empty($userData['setor'])) {
                return ['success' => false, 'message' => 'Setor do User é obrigatório'];
            }

            $userData['nome'] = trim(strip_tags($userData['nome']));
            $userData['telefone'] = preg_replace('/[^0-9]/', '', $userData['telefone']);
            $userData['setor'] = trim(strip_tags($userData['setor']));
            $senhaHash = password_hash(trim($userData['senha']), PASSWORD_DEFAULT);

            $user = new Users([
                'nome' => $userData['nome'] ?? '',
                'telefone' => $userData['telefone'] ?? '',
                'senha' => $senhaHash,
                'setor' => $userData['setor'] ?? 'SEM SETOR'
            ]);

            $result = $this->repositoryUsuarios->create($user);

            return $result
                ? ['success' => true, 'message' => 'User criado com sucesso']
                : ['success' => false, 'message' => 'Erro ao salvar User'];
        } catch (Exception $e) {
            error_log("Erro ao criar User: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }






    public function updateUserPopUp($idUser, array $data) //nome, numero e senha = PUBLIC mas nao retorna nada
    {
        try {

            $check = $this->validarUserComumRequest($idUser);
            if ($check !== true) {
                return $check;
            }

            $data['telefone_user'] = preg_replace('/[^0-9]/', '', $data['telefone_user']);
            if (!empty($data['senha_user'])) {
                $data['senha'] = password_hash(trim($data['senha_user']), PASSWORD_DEFAULT);
            }



            $success = $this->repositoryUsuarios->updateUserPopUp($idUser, $data);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'User atualizado com sucesso',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar User no banco de dados'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro no updateUserPopUPService update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao atualizar User: ' . $e->getMessage()
            ];
        }
    }



    public function updateUserAdmin($idUser, array $data, $dadosUserRequest) // idUser, dados de edição, dados de requisição
    {
        try {
            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }

            $updateData = [];

            $updateData['nome'] = trim(strip_tags($data['nome'] ?? ''));

            $updateData['telefone'] = preg_replace('/[^0-9]/', '', $data['telefone'] ?? '');

            $updateData['setor'] = trim(strip_tags($data['setor'] ?? ''));

            if (!empty($data['senha'])) { // Frontend envia 'senha'
                $updateData['senha'] = password_hash(trim($data['senha']), PASSWORD_DEFAULT);
            }

            if (isset($updateData['id'])) {
                unset($updateData['id']);
            }
            $success = $this->repositoryUsuarios->updateUserAdmin($idUser, $updateData);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'User atualizado com sucesso',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar User no banco de dados'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro no updateUserAdmin update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao atualizar User: ' . $e->getMessage()
            ];
        }
    }




    public function filtroUserNINSadm($valor, $dadosUserRequest)
    {
        try {

            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }


            $valor = trim(strip_tags($valor));
            $valor = preg_replace('/[^0-9]/', '', $valor);


            $resultado = $this->repositoryUsuarios->filtroUserNINSadm($valor);

            if (!empty($resultado)) {
                return [
                    'success' => true,
                    'data' => $resultado,
                    'message' => 'valor x encontrado'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'valor x não encontrado'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar valor : " . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar valor x'
            ];
        }
    }



    public function login($nome, $senha)
    {
        try {

            $nome = trim(strip_tags($nome));

            $user = $this->repositoryUsuarios->loginName($nome);

            if (!$user || !password_verify($senha, $user['senha'])) {
                return ['success' => false, 'message' => 'Credenciais inválidas'];
            }
            $idUser = $this->puxarId($nome);

            if (!$idUser['success'] || empty($idUser['data'])) {
                return ['success' => false, 'message' => 'Erro ao buscar ID do usuário.'];
            }

            $idUser = $idUser['data']['id_user'] ?? null;


            $validaAdmin = $this->validaAdminExibicao($idUser);

            if (!$validaAdmin['success']) {
                return [
                    'success' => true,
                    'id' => $idUser,
                    'isAdmin' => false,
                    'message' => 'Login realizado com sucesso'
                ];
            }

            return [
                'success' => true,
                'id' => $idUser,
                'isAdmin' => true,
                'message' => 'Login realizado com sucesso.',
            ];
        } catch (Exception $e) {
            error_log(" Erro UserService login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno ao realizar login.'
            ];
        }
    }



    public function puxarId($nome)
    {
        try {
            $nome = trim(strip_tags($nome));
            if (empty($nome) || !is_string($nome)) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'Nome de usuário inválido.'
                ];
            }

            $user = $this->repositoryUsuarios->filtroUserId($nome);

            if (!$user || empty($user['id'])) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'Usuário não encontrado.'
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'id_user' => $user['id']
                ]
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar o Id: " . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar Id'
            ];
        }
    }
}
