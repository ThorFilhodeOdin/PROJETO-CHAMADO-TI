<?php
class routes
{

    //20
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    public function __construct()
    {
        $this->registerRoutes();
    }


    private function registerRoutes()
    {

        $this->routes['POST']['/api/validaAdminExibicaoChamados'] = function () {
            $controller = new ChamadosController();
            $idUser = $_POST['idUser'] ?? null;
            return $controller->validaAdminExibicao($idUser);
        };

        $this->routes['POST']['/api/validaAdminExibicaoUser'] = function () {
            $controller = new UserController();
            $idUser = $_POST['idUser'] ?? null;
            return $controller->validaAdminExibicao($idUser);
        };

        $this->routes['GET']['/api/getAllUsersAdmin'] = function ($query_params) {
            $controller = new UserController();
            $requestData = ['idUserSession' => $query_params['idUserSession']];
            return $controller->getAllUsersAdmin($requestData);
        };

        $this->routes['GET']['/api/MyUser'] = function ($query_params) {
            $controller = new UserController();
            return $controller->getMyUser($query_params['$myId']);
        };

        $this->routes['POST']['/api/createUser'] = function () {
            $controller = new UserController();
            $idUserSession = $_POST['idUserSession'] ?? null;
            return $controller->createUser($_POST, ['idUserSession' => $idUserSession]);
        };

        $this->routes['DELETE']['/api/deleteUserAdmin'] = function () { //nao da pra fazer fecht fora da url com delete
            $controller = new UserController();
            $idUser = $_GET['id'] ?? null;
            $idUserSession = $_GET['idUserSession'] ?? null;
            return $controller->deleteUser($idUser, ['idUserSession' => $idUserSession]);
        };


        $this->routes['GET']['/api/pesquisarfiltroUserNINSadm'] = function ($query_params) {
            $controller = new UserController();
            $valor = $query_params['valor'] ?? '';
            /* USERS
            setor
            numero
            nome
            id_chamado
            NINS
           */
            return $controller->filtroUserNINSadm($valor, ['idUserSession' => (int)$query_params['idUserSession']]);
        };


        $this->routes['POST']['/api/updateUserPopUp'] = function () { //id user, data
            ob_start();
            $controller = new UserController();
            $idUser = $_POST['idUser'] ?? null;
            $data = $_POST;
            return $controller->updateUserPopUp($idUser, $data);
        };


        $this->routes['POST']['/api/updateUserAdmin'] = function () {

            $controller = new UserController();
            $idUserEditado = $_POST['idUserEditado'] ?? null;
            $idUserSession = $_POST['idUserSession'] ?? null;
            $data = $_POST;
            return $controller->updateUserAdmin($idUserEditado, $data, ['id' => $idUserSession]);
        };

        /*
        $this->routes['POST']['/api/login'] = function () {
            header('Content-Type: application/json; charset=utf-8');
            $controller = new UserController();
            $input = json_decode(file_get_contents('php://input'), true);
            $nome = trim($input['nome']);
            $senha = trim($input['senha']);
            return $controller->login($nome, $senha);
        };
*/

        $this->routes['POST']['/api/login'] = function () {
            header('Content-Type: application/json; charset=utf-8');

            $input = json_decode(file_get_contents('php://input'), true);

            if (!is_array($input) || empty($input['nome']) || empty($input['senha'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nome e senha são obrigatórios.']);
                exit;
            }

            $controller = new UserController();
            $response = $controller->login($input['nome'], $input['senha']);

            // Garante que nada será enviado antes
            http_response_code($response['success'] ? 200 : 401);
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        };








        //CHAMADOS ROUTES
        $this->routes['GET']['/api/getMeusChamados'] = function ($query_params) {
            $controller = new ChamadosController(); //reaproveitar api no popup de update
            $idUser = $query_params['idUser'];
            /*
                oq retorna:
                'idChamado' => idChamado
                'nome' => $query_params['nome'],

                'numero' => $query_params['numero'],
                'tipo' => $query_params['tipo'],
                'setor' => $query_params['setor'],
                'data' => $query_params['data'],

                'descricao' => $query_params['descricao'],
                'foto1' => $query_params['foto1'] ?? null,
                'foto2' => $query_params['foto2'] ?? null,
                'foto3' => $query_params['foto3'] ?? null,
                'foto4' => $query_params['foto4'] ?? null,
                'foto5' => $query_params['foto5'] ?? null,
                'foto6' => $query_params['foto6'] ?? null
            */
            return $controller->getMeusChamados($idUser);
        };



        $this->routes['GET']['/api/getAllChamadosAdmin'] = function ($query_params) {
            $controller = new ChamadosController();
            return $controller->getAllChamadosAdmin([
                'id' => $query_params['idUserSession'] ?? ''
            ]);
        };
        $this->routes['GET']['/api/getChamadoById'] = function ($query_params) {
            $controller = new ChamadosController();
            return $controller->getChamadoById([
                'id' => $query_params['id'] ?? '',
                'idChamado'     => $query_params['idChamado'] ?? ''
            ]);
        };




        $this->routes['POST']['/api/createChamadoComum'] = function () {
            $controller = new ChamadosController();

            $idUser = $_POST['idUser'] ?? null;
            if (!$idUser) {
                echo json_encode(['success' => false, 'message' => 'ID de usuário não enviado']);
                exit;
            }

            $data = $_POST;
            if (!empty($_FILES['fotos'])) {
                $data['fotos'] = $_FILES['fotos'];
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($controller->createChamadoComum($idUser, $data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        };




        $this->routes['POST']['/api/updateChamadoComum'] = function () { //extrair idchamado user e apagar todos os dados
            $controller = new ChamadosController();
            $idChamado = $_POST['idChamado'];
            $idUser = $_POST['idUser'];
            $data = $_POST;
            /*
            'idUser' => post['idUser'],  // vindo de uma  constante no site, que vem da api login
            idChamado = // vindo getMeusChamados
            tipo  = modificavel  
            descriçao    = modificavel 
            fotos ate 6  = modificavel 
            (update gerado em cima da api de getMeusChamados)
            */
            return $controller->updateChamadoComum($idUser, $idChamado, $data);
        };



        $this->routes['GET']['/api/pesquisarfiltroUserNINSTadm'] = function ($query_params) {
            $controller = new UserController();
            $valor = $query_params['valor'];
            /* CHAMADOS
            setor
            tipo
            numero
            nome
            id_chamado
            NINST
            */
            return $controller->filtroUserNINSadm($valor, (int)$query_params['idUserSession']);
        };





        $this->routes['DELETE']['/api/deleteChamado'] = function ($query_params) {
            $controller = new ChamadosController();
            $idChamado = $query_params['idChamado'];
            return $controller->deleteChamado($idChamado, $query_params['idUserSession']);
        };



        $this->routes['POST']['/api/updateChamadoAbertoAdmin'] = function () {
            $controller = new ChamadosController();
            $idChamado = $_POST['idChamado'] ?? null;
            $data = $_POST;
            $idUserSession = $_POST['idUser'];
            return $controller->updateChamadoAbertoAdmin($idUserSession, $idChamado, $data);
        };


        $this->routes['GET']['/api/CarregarTodosChamadosTi'] = function ($query_params) {
            $controller = new ChamadosController();
            return $controller->CarregarTodosChamadosTi([
                'id' => $query_params['idUserSession'] ?? ''
            ]);
        };

        $this->routes['POST']['/api/finalizarChamadoAdmin'] = function () {
            $controller = new ChamadosController();
            $idUserSession = $_POST['idUser'];
            $idChamado = $_POST['idChamado'] ?? null;
            return $controller->finalizarChamadoAdmin($idUserSession, $idChamado);
        };


        $this->routes['POST']['/api/reabrirChamadoAdmin'] = function () {
            $controller = new ChamadosController();
            $idUserSession = $_POST['idUser'];
            $idChamado = $_POST['idChamado'] ?? null;
            return $controller->reabrirChamadoAdmin($idUserSession, $idChamado);
        };





        $this->routes['POST']['/api/createChamadoAdmin'] = function () {
            $controller = new ChamadosController();
            $idUserSession = $_POST['idUser'];
            $data = $_POST;
            if (!empty($_FILES)) {
                $data['fotos'] = $_FILES;
            }
            /*  
            idUserSession de lei

            status aberto 
            tipo (manual)
            descriçao  (manual)
            fotos ate 6 (manual)
            data (manual)
            setor (manual) 
            nome (manual)
            numero (manual)

            id  //  gerado no back  

            */
            return $controller->createChamadoAdmin($idUserSession, $data);
        };
    }






    //funçoes padrao do routes
    public function handle($method, $path, $query_string)
    {
        try {
            parse_str($query_string, $query_params);

            if (isset($this->routes[$method][$path])) {
                $handler = $this->routes[$method][$path];
                $response = $handler($query_params);
                $this->sendResponse($response);
                return;
            }

            foreach ($this->routes[$method] as $route => $handler) {
                if (strpos($route, '{') !== false) {
                    $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
                    $pattern = str_replace('/', '\/', $pattern);

                    if (preg_match("/^{$pattern}$/", $path, $matches)) {
                        $param = $matches[1] ?? null;
                        $response = $handler($query_params, $param);
                        $this->sendResponse($response);
                        return;
                    }
                }
            }

            $this->sendResponse([
                'success' => false,
                'message' => 'Rota não encontrada',
                'path' => $path,
                'method' => $method
            ], 404);
        } catch (Exception $e) {
            error_log("Erro no roteador: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    private function sendResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
