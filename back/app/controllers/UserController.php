<?php
class UserController
{
    private $UserService;

    public function __construct()
    {
        $this->UserService = new UserService();
    }


    public function validaAdminExibicao($dadosUserRequest)
    {
        $result = $this->UserService->validaAdminExibicao($dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getAllUsersAdmin($dadosUserRequest)
    {
        return $this->UserService->getAllUsersAdmin($dadosUserRequest);
    }

    public function getMyUser($myId)
    {
        return $this->UserService->getMyUser($myId);
    }


    public function createUser($userData, $dadosUserRequest)
    {
        $result = $this->UserService->createUser($userData, $dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }



    public function deleteUser($userid, $dadosUserRequest)
    {
        $result = $this->UserService->deleteUser($userid, $dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }




    public function updateUserPopUp($idUser, array $data)
    {
        try {
            header('Content-Type: application/json; charset=utf-8');

            if (empty($idUser)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID inválido'
                ]);
                exit;
            }

            if (empty($data)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Dados do User não enviados'
                ]);
                exit;
            }

            $result = $this->UserService->updateUserPopUp($idUser, $data);

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erro no controllerUpdateUserAdmin: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno controllerUpdateUserAdmin: ' . $e->getMessage()
            ]);
            exit;
        }
    }



    public function updateUserAdmin($idUser, array $data, $dadosUserRequest)
    {
        try {
            header('Content-Type: application/json; charset=utf-8');

            if (empty($idUser)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID do usuário para edição ausente ou inválido.'
                ]);
                exit;
            }

            if (empty($data) || empty($dadosUserRequest)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Dados de requisição incompletos.'
                ]);
                exit;
            }

            $result = $this->UserService->updateUserAdmin($idUser, $data, $dadosUserRequest);
            header('Content-Type: application/json; charset=utf-8');
            if (!$result['success']) {
                http_response_code(400); // Erro de regra de negócio
            } else {
                http_response_code(200); // Sucesso
            }

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erro no controllerUpdateUserAdmin: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno controllerUpdateUserAdmin: ' . $e->getMessage()
            ]);
            exit;
        }
    }


    public function filtroUserNINSadm($valor, $dadosUserRequest)
    {
        header('Content-Type: application/json; charset=utf-8');
        $result = $this->UserService->filtroUserNINSadm($valor, $dadosUserRequest);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }


    public function login($nome, $senha)
    {
        try {
            if (empty($nome) || empty($senha)) {
                return [
                    'success' => false,
                    'message' => 'Nome e senha são obrigatórios.'
                ];
            }

            $result = $this->UserService->login($nome, $senha);
            return $result;
        } catch (Exception $e) {
            error_log("Erro no controller login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno no servidor.'
            ];
        }
    }
}
