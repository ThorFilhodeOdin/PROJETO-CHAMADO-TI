<?php

class ChamadosService
{
    private $repositoryChamados;

    public function __construct()
    {
        $database = new Database();
        $this->repositoryChamados = new repositoryChamados($database);
    }



    private function validarAdmin($idUser)
    {
        try {

            $id = trim(strip_tags($idUser['id'] ?? ''));
            if (empty($id) || !preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
                return ['success' => false, 'message' => 'ID do usuário inválido'];
            }
            $isAdmin = $this->repositoryChamados->filtroUserAdminId($id);

            if (!$isAdmin) {

                return ['success' => false, 'message' => 'Usuário sem permiss'];
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro na validação de admin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno ao validar usuário'];
        }
    }



    private function validarUserComumRequest($idUser)
    { //ID USER NAO É UM ARRAY
        {
            //so da pra acessar um endpoint com um id de user valido
            //verificaçao de cliente ser verdadeiro por id, e ja puxa os chamados de acordo com o id
            //(se o invasor conseguir forjar um id ele ja sabe o nome,
            //mas nao consegue logar por conta da senha, mas puxar os chamados consegue), 
            //muito dificil alguem que nao tem acesso ao banco saber ids, 
            //so se for vazado voluariamente
            try {
                $id = trim(strip_tags($idUser)); //ID USER NAO É UM ARRAY
                if (empty($id) || !preg_match('/^[0-9a-fA-F-]{36}$/', $id)) { //caso tente acessar qualquer api diretamente sem id nao vai conseguir 
                    return ['success' => false, 'message' => 'ID do usuário inválido'];
                }
                $isValid = $this->repositoryChamados->filtroUserComumId($id);
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

            // ✅ REGEX CORRIGIDA para UUID
            if (empty($id) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
                return ['success' => false, 'message' => 'ID do usuário inválido'];
            }

            $isAdmin = $this->repositoryChamados->filtroUserAdminId($id);
            if (!$isAdmin) {
                return ['success' => false, 'message' => 'Usuário sem permissão'];
            }

            return ['success' => true, 'message' => 'Usuário autorizado'];
        } catch (Exception $e) {
            error_log("Erro na validação de admin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno ao validar usuário'];
        }
    }


    public function getMeusChamados($idUser)
    {
        try {

            $check = $this->validarUserComumRequest($idUser);
            if ($check !== true) {
                return $check;
            }
            $chamados = $this->repositoryChamados->getMeusChamados($idUser); //chamados se relaciona com o user pelo nome

            return [
                'success' => true,
                'data' => $chamados,
                'message' => 'Chamado carregados com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar todos os Users: " . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar Users'
            ];
        }
    }




    public function createChamadoComum($idUser, $data)
    {
        try {
            $check = $this->validarUserComumRequest($idUser);
            if ($check !== true) {
                return $check;
            }

            // Puxar dados do usuário (nome, número, setor)
            $NNS = $this->repositoryChamados->filtroUserComumId($idUser);
            if (!$NNS) {
                return ['success' => false, 'message' => 'Usuário não encontrado'];
            }

            $data['nome'] = trim(strip_tags($NNS['nome']));
            $data['numero'] = trim(strip_tags($NNS['telefone']));
            $data['setor'] = trim(strip_tags($NNS['setor']));
            $data['data'] = date('Y-m-d'); // data automática

            $requiredFields = ['tipo', 'descricao'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "O campo {$field} é obrigatório"];
                }
            }

            $data['tipo'] = trim(strip_tags($data['tipo']));
            $data['descricao'] = trim(strip_tags($data['descricao']));
            $data['status'] = 'aberto';

            // ===== PROCESSAR FOTOS =====
            $imagens = [];
            if (!empty($data['fotos'])) {
                for ($i = 0; $i < 6; $i++) {
                    $imagens["imagem" . ($i + 1)] = !empty($data['fotos']['tmp_name'][$i])
                        ? file_get_contents($data['fotos']['tmp_name'][$i])
                        : null;
                }
            } else {
                for ($i = 1; $i <= 6; $i++) {
                    $imagens["imagem{$i}"] = null;
                }
            }

            $Chamado = new Chamados([
                'nome' => $data['nome'],
                'numero' => $data['numero'],
                'tipo' => $data['tipo'],
                'descricao' => $data['descricao'],
                'setor' => $data['setor'],
                'status' => $data['status'],
                'data' => $data['data'],
                'imagem1' => $imagens['imagem1'],
                'imagem2' => $imagens['imagem2'],
                'imagem3' => $imagens['imagem3'],
                'imagem4' => $imagens['imagem4'],
                'imagem5' => $imagens['imagem5'],
                'imagem6' => $imagens['imagem6'],
            ]);

            $result = $this->repositoryChamados->create($Chamado);

            return $result
                ? ['success' => true, 'message' => 'Chamado criado com sucesso']
                : ['success' => false, 'message' => 'Erro ao salvar chamado'];
        } catch (Exception $e) {
            error_log("Erro ao criar Chamado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }




    public function createChamadoAdmin($dadosUserRequest, $data)
    {
        try {

            if (isset($dadosUserRequest['idUserSession'])) {
                $dadosUserRequest['id'] = $dadosUserRequest['idUserSession'];
            } else if (!isset($dadosUserRequest['id'])) {
                $dadosUserRequest['id'] = '';
            }
            $check = $this->validarAdmin(['id' => $dadosUserRequest['id'] ?? '']);
            error_log("DEBUG: Validacao Admin Result: " . print_r($check, true));
            if ($check !== true) {
                return $check;
            }

            $data['nome']      = isset($data['nome'])      ? trim(strip_tags($data['nome']))      : null;
            $data['numero']    = isset($data['numero'])    ? trim(strip_tags($data['numero']))    : null;
            $data['tipo']      = isset($data['tipo'])      ? trim(strip_tags($data['tipo']))      : null;
            $data['descricao'] = isset($data['descricao']) ? trim(strip_tags($data['descricao'])) : null;
            $data['data']      = isset($data['data'])      ? trim(strip_tags($data['data']))      : null;
            $data['setor']     = isset($data['setor'])     ? trim(strip_tags($data['setor']))     : null;
            $data['status']    = 'aberto';


            $required = ['tipo', 'descricao', 'nome', 'numero', 'setor', 'data'];
            foreach ($required as $campo) {
                if (empty($data[$campo])) {
                    return [
                        'success' => false,
                        'message' => "O campo '{$campo}' é obrigatório."
                    ];
                }
            }
            $fileData = $data['fotos']['fotos'] ?? $data['fotos'];
            
            error_log("DEBUG: createChamadoAdmin - fileData: " . print_r($fileData, true));

            $imagens = [];
            if (
                !empty($fileData) &&
                isset($fileData['tmp_name']) &&
                is_array($fileData['tmp_name'])
            ) {
                for ($i = 0; $i < 6; $i++) {
                    $tmpName = $fileData['tmp_name'][$i] ?? null;
                    $error = $fileData['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    
                    error_log("DEBUG: Processing image $i - tmpName: $tmpName, error: $error");

                    // 🛑 CRÍTICO: Verifica se o upload foi bem-sucedido e o arquivo existe
                    if ($tmpName && $error === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                        $imagens["imagem" . ($i + 1)] = file_get_contents($tmpName);
                        error_log("DEBUG: Image $i loaded successfully.");
                    } else {
                        $imagens["imagem" . ($i + 1)] = null;
                        error_log("DEBUG: Image $i failed or empty.");
                    }
                }
            } else {
                error_log("DEBUG: No valid file data found.");
                // Caso não haja uploads válidos, preenche com null para o Model
                for ($i = 1; $i <= 6; $i++) {
                    $imagens["imagem{$i}"] = null;
                }
            }
            $Chamado = new Chamados([
                'nome'      => $data['nome'],
                'numero'    => $data['numero'],
                'tipo'      => $data['tipo'],
                'descricao' => $data['descricao'],
                'setor'     => $data['setor'],
                'status'    => $data['status'],
                'data'      => $data['data'],
                'imagem1' => $imagens['imagem1'],
                'imagem2' => $imagens['imagem2'],
                'imagem3' => $imagens['imagem3'],
                'imagem4' => $imagens['imagem4'],
                'imagem5' => $imagens['imagem5'],
                'imagem6' => $imagens['imagem6'],
            ]);
            error_log("DEBUG: Dados do Chamado para Insercao: " . print_r($Chamado->toArray(), true));
            $result = $this->repositoryChamados->create($Chamado);

            return $result
                ? ['success' => true, 'message' => 'Chamado criado com sucesso']
                : ['success' => false, 'message' => 'Erro ao salvar Chamado'];
        } catch (Exception $e) {
            error_log("Erro ao criar Chamado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }






    public function updateChamadoComum($idUser, $idChamado, $data)
    {
        try {

            $check = $this->validarUserComumRequest($idUser);
            if ($check !== true) {
                return $check;
            }


            //idChamado sanitizado e o data tambem : tipo e descriçao 
            $idChamado = (int) $idChamado;
            $data['tipo'] = isset($data['tipo']) ? trim(strip_tags($data['tipo'])) : null;
            $data['descricao'] = isset($data['descricao']) ? trim(strip_tags($data['descricao'])) : null;


            //alteraçao de fotos(metodos de exclusao e adiçao de fotos)
            if (!empty($data['fotos'])) {
                $fotos = $data['fotos'];
                for ($i = 1; $i <= 6; $i++) {
                    if (isset($fotos[$i - 1]['tmp_name']) && is_uploaded_file($fotos[$i - 1]['tmp_name'])) {
                        // adiciona/substitui imagem
                        $data["imagem{$i}"] = file_get_contents($fotos[$i - 1]['tmp_name']);
                    } elseif (isset($fotos[$i - 1]['delete']) && $fotos[$i - 1]['delete'] === true) {
                        // exclusão solicitada
                        $data["imagem{$i}"] = null;
                    }
                }
            }

            $success = $this->repositoryChamados->updateChamadoComum($idChamado, $data);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Chamado atualizado com sucesso',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar Chamado no banco de dados'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro no serviceupdateChamadoComum update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao atualizar Chamado: ' . $e->getMessage()
            ];
        }
    }



    public function getAllChamadosAdmin($dadosUserRequest)
    {
        try {
            $check = $this->validarAdmin(['id' => $dadosUserRequest['id'] ?? '']);
            if ($check !== true) {
                return $check;
            }

            $Chamados = $this->repositoryChamados->findAll();

            foreach ($Chamados as &$c) {
                unset(
                    $c['descricao'],
                    $c['imagem1'],
                    $c['imagem2'],
                    $c['imagem3'],
                    $c['imagem4'],
                    $c['imagem5'],
                    $c['imagem6'],
                    $c['numero']
                );
            }
            unset($c);

            return [
                'success' => true,
                'data'    => $Chamados,
                'total'   => count($Chamados),
                'message' => 'Chamados carregados com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar todos os Chamados: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => [],
                'total'   => 0,
                'message' => 'Erro ao carregar Chamados'
            ];
        }
    }


    public function CarregarTodosChamadosTi($dadosUserRequest)
    {
        try {
            $check = $this->validarAdmin(['id' => $dadosUserRequest['id'] ?? '']);
            if ($check !== true) {
                return $check;
            }

            $Chamados = $this->repositoryChamados->findAllTi();

            foreach ($Chamados as &$c) {
                unset(
                    $c['descricao'],
                    $c['imagem1'],
                    $c['imagem2'],
                    $c['imagem3'],
                    $c['imagem4'],
                    $c['imagem5'],
                    $c['imagem6'],
                    $c['numero']
                );
            }
            unset($c);

            return [
                'success' => true,
                'data'    => $Chamados,
                'total'   => count($Chamados),
                'message' => 'Chamados carregados com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar todos os Chamados: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => [],
                'total'   => 0,
                'message' => 'Erro ao carregar Chamados'
            ];
        }
    }



    public function getChamadoById($dadosUserRequest)
    {
        try {
            // Valida se o usuário é admin
            $check = $this->validarAdmin(['id' => $dadosUserRequest['id'] ?? '']);
            if ($check !== true) {
                return $check; // retorna erro se não for admin
            }

            // Busca o chamado pelo idChamado
            $idChamado = $dadosUserRequest['idChamado'] ?? null;
            if (!$idChamado) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'ID do chamado não informado'
                ];
            }

            $chamado = $this->repositoryChamados->findById($idChamado);

            if (!$chamado) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Chamado não encontrado'
                ];
            }
            foreach (['imagem1', 'imagem2', 'imagem3', 'imagem4', 'imagem5', 'imagem6'] as $img) {
                if (!empty($chamado[$img])) {
                    $chamado[$img] = base64_encode($chamado[$img]);
                }
            }

            return [
                'success' => true,
                'data'    => $chamado,
                'message' => 'Chamado carregado com sucesso'
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar chamado: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Erro ao carregar chamado'
            ];
        }
    }








    public function filtroUserNINSTadm($valor, $dadosUserRequest)
    {
        try {

            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }


            $valor = trim(strip_tags($valor));
            $valor = preg_replace('/[^0-9]/', '', $valor);


            $resultado = $this->repositoryChamados->filtroUserNINSTadm($valor);

            if (!empty($resultado)) {
                return [
                    'success' => true,
                    'data' => $resultado,
                    'message' => 'valor(es) x encontrado'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'valor(es) x não encontrado'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar valor(es) : " . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar valor(es) x'
            ];
        }
    }












    public function deleteChamado($idChamado, $idUserSession)
    {
        try {
            $dadosParaValidacao = ['id' => $idUserSession];

            $check = $this->validarAdmin($dadosParaValidacao);
            if ($check !== true) {
                return $check;
            }

            if (empty($idChamado) || !is_numeric($idChamado) || $idChamado <= 0) {
                return ['success' => false, 'message' => 'ID do chamado inválido'];
            }

            $existingChamado = $this->repositoryChamados->filtroChamadoId($idChamado);
            if (!$existingChamado) {
                return ['success' => false, 'message' => 'Chamado não encontrado'];
            }

            $result = $this->repositoryChamados->delete($idChamado);

            return $result
                ? ['success' => true, 'message' => 'Chamado deletado com sucesso']
                : ['success' => false, 'message' => 'Erro ao deletar chamado'];
        } catch (Exception $e) {
            error_log("Erro no deleteChamado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno ao deletar chamado'];
        }
    }



    public function updateChamadoAbertoAdmin($dadosUserRequest, $idChamado, $data)
    {
        try {
            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }

            $idChamado = (int) $idChamado;

            if (empty($idChamado) || !is_numeric($idChamado) || $idChamado <= 0) {
                return ['validaçao de valor id_chamado true' => false, 'message' => 'ID do chamado inválido'];
            }

            $existingChamado = $this->repositoryChamados->filtroChamadoId($idChamado);
            if (!$existingChamado) {
                return ['validaçao de existencia de id_chamado true' => false, 'message' => 'Chamado não encontrado'];
            }

            $dadosMapeados = [];

            $dadosMapeados['tipo']      = isset($data['callType'])      ? trim(strip_tags($data['callType']))      : null;
            $dadosMapeados['descricao'] = isset($data['description'])   ? trim(strip_tags($data['description']))   : null;
            $dadosMapeados['nome']      = isset($data['nomeUsuario'])   ? trim(strip_tags($data['nomeUsuario']))   : null;
            $dadosMapeados['numero']    = isset($data['numeroChamado']) ? trim(strip_tags($data['numeroChamado'])) : null;
            $dadosMapeados['data']      = isset($data['dataChamado'])   ? trim(strip_tags($data['dataChamado']))   : date('Y-m-d');
            $dadosMapeados['setor']     = isset($data['setor'])         ? trim(strip_tags($data['setor']))         : null;
            $dadosMapeados['status']    = isset($data['status'])        ? trim(strip_tags($data['status']))        : null;
            $dadosMapeados['idUser']    = isset($data['idUser'])        ? trim(strip_tags($data['idUser']))        : null;

            $imagensAtuaisBinario = [];

            $imagensParaDeletarBase64 = [];
            if (isset($data['imagensParaDeletar'])) {
                $tempArray = json_decode($data['imagensParaDeletar'], true);
                if (is_array($tempArray)) {
                    $imagensParaDeletarBase64 = array_map('trim', $tempArray);
                }
            }

            for ($i = 1; $i <= 6; $i++) {
                $campo = "imagem{$i}";
                $srcExistenteBinario = $existingChamado[$campo]; // Conteúdo BLOB

                if (!empty($srcExistenteBinario) && is_string($srcExistenteBinario)) {

                    $srcExistenteBase64 = base64_encode($srcExistenteBinario);
                    $srcExistenteBase64 = trim($srcExistenteBase64);

                    if (!in_array($srcExistenteBase64, $imagensParaDeletarBase64)) {
                        $imagensAtuaisBinario[] = $srcExistenteBinario;
                    }
                }
            }

            if (isset($_FILES['novasImagens']) && is_array($_FILES['novasImagens']['tmp_name'])) {
                $count = count($_FILES['novasImagens']['tmp_name']);
                for ($i = 0; $i < $count; $i++) {
                    if (is_uploaded_file($_FILES['novasImagens']['tmp_name'][$i])) {
                        $imagensAtuaisBinario[] = file_get_contents($_FILES['novasImagens']['tmp_name'][$i]);
                    }
                }
            }

            $imagensFinaisBinario = array_slice($imagensAtuaisBinario, 0, 6);

            for ($i = 1; $i <= 6; $i++) {
                $dadosMapeados["imagem{$i}"] = null;
            }

            foreach ($imagensFinaisBinario as $index => $binaryContent) {
                $dadosMapeados["imagem" . ($index + 1)] = $binaryContent;
            }

            $success = $this->repositoryChamados->updateChamadoAbertoAdmin($idChamado, $dadosMapeados);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Chamado atualizado com sucesso',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar Chamado no banco de dados'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro no service updateChamadoAbertoAdmin: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao atualizar Chamado: ' . $e->getMessage()
            ];
        }
    }



    public function finalizarChamadoAdmin($dadosUserRequest, $idChamado)
    {
        try {

            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }

            $idChamado = (int) $idChamado;

            if (empty($idChamado) || !is_numeric($idChamado) || $idChamado <= 0) {
                return ['validaçao de valor id_chamado true' => false, 'message' => 'ID do chamado inválido'];
            }
            $existingChamado = $this->repositoryChamados->filtroChamadoId($idChamado);
            if (!$existingChamado) {
                return ['validaçao de existencia de id_chamado true' => false, 'message' => 'Chamado não encontrado'];
            }

            $success = $this->repositoryChamados->finalizarChamadoAdmin($idChamado);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Chamado atualizado com sucesso',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar finalizar no banco de dados'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro no servicefinalizarChamadoAdmin update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao finalizar Chamado: ' . $e->getMessage()
            ];
        }
    }





    public function reabrirChamadoAdmin($dadosUserRequest, $idChamado)
    {
        try {

            $check = $this->validarAdmin($dadosUserRequest);
            if ($check !== true) {
                return $check;
            }

            $idChamado = (int) $idChamado;

            if (empty($idChamado) || !is_numeric($idChamado) || $idChamado <= 0) {
                return ['validaçao de valor id_chamado true' => false, 'message' => 'ID do chamado inválido'];
            }
            $existingChamado = $this->repositoryChamados->filtroChamadoId($idChamado);
            if (!$existingChamado) {
                return ['validaçao de existencia de id_chamado true' => false, 'message' => 'Chamado não encontrado'];
            }

            $success = $this->repositoryChamados->reabrirChamadoAdmin($idChamado);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Chamado reaberto com sucesso',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao reabrir chamado no banco de dados'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro no servicereabrirChamadoAdmin: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao reabrir Chamado: ' . $e->getMessage()
            ];
        }
    }
}
