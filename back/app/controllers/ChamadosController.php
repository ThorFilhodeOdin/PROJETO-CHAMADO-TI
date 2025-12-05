<?php
class ChamadosController
{
    private $ChamadosService;

    public function __construct()
    {
        $this->ChamadosService = new ChamadosService();
    }



    public function validaAdminExibicao($dadosUserRequest)
    {
        $result = $this->ChamadosService->validaAdminExibicao($dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }


    public function getMeusChamados($idUser)
    {
        $result = $this->ChamadosService->getMeusChamados($idUser);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function CarregarTodosChamadosTi($dadosUserRequest)
    {
        $result = $this->ChamadosService->CarregarTodosChamadosTi($dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    public function getAllChamadosAdmin($dadosUserRequest)
    {
        $result = $this->ChamadosService->getAllChamadosAdmin($dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    public function getChamadoById($dadosUserRequest)
    {
        $result = $this->ChamadosService->getChamadoById($dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function createChamadoComum($idUser, $data)
    {
        $result = $this->ChamadosService->createChamadoComum($idUser, $data);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function createChamadoAdmin($idUserSession, $data)
    {
        $dadosParaValidacao = ['id' => $idUserSession];
        $result = $this->ChamadosService->createChamadoAdmin($dadosParaValidacao, $data);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }


    public function updateChamadoComum($idUser, $idChamado, $data)
    {
        try {

            if (empty($idUser) || $idUser <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'IDChamado inválido'
                ]);
                exit;
            }

            if (empty($idChamado) || $idChamado <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'IDChamado inválido'
                ]);
                exit;
            }

            if (empty($data)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Dados do Chamado não enviados'
                ]);
                exit;
            }

            $result = $this->ChamadosService->updateChamadoComum($idUser, $idChamado, $data);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erro no controllerupdateChamadoComum: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno  controllerupdateChamadoComum: ' . $e->getMessage()
            ]);
            exit;
        }
    }




    public function filtroUserNINSTadm($valor, $dadosUserRequest)
    {
        header('Content-Type: application/json; charset=utf-8');
        $result = $this->ChamadosService->filtroUserNINSTadm($valor, $dadosUserRequest);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }





    public function deleteChamado($idChamado, $dadosUserRequest)
    {
        $result = $this->ChamadosService->deleteChamado($idChamado, $dadosUserRequest);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }





    public function updateChamadoAbertoAdmin($idUserSession, $idChamado, $data)
    {
        try {
            header('Content-Type: application/json; charset=utf-8');
            $dadosParaValidacao = ['id' => $idUserSession];
            $idChamado = (int) $idChamado;
            $result = $this->ChamadosService->updateChamadoAbertoAdmin($dadosParaValidacao, $idChamado, $data);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erro no controllerupdateChamadoAbertoAdmin: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno controllerupdateChamadoAbertoAdmin: ' . $e->getMessage()
            ]);
            exit;
        }
    }





    public function finalizarChamadoAdmin($dadosUserRequest, $idChamado)
    {
        try {
            header('Content-Type: application/json; charset=utf-8');
            $dadosParaValidacao = ['id' => $dadosUserRequest];

            $idChamado = (int) $idChamado;
            $result = $this->ChamadosService->finalizarChamadoAdmin($dadosParaValidacao, $idChamado);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erro no controllerfinalizarChamadoAdmin: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno controllerfinalizarChamadoAdmin: ' . $e->getMessage()
            ]);
            exit;
        }
    }



    public function reabrirChamadoAdmin($idUserSession, $idChamado)
    {
        try {
            header('Content-Type: application/json; charset=utf-8');

            $dadosParaValidacao = ['id' => $idUserSession];
            $idChamado = (int) $idChamado;

            $result = $this->ChamadosService->reabrirChamadoAdmin($dadosParaValidacao, $idChamado);

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erro no controller reabrirChamadoAdmin: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno controller reabrirChamadoAdmin: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
