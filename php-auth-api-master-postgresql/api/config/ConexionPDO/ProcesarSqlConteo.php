<?php

class ProcesarSqlConteo {

    private $offset;
    private $pagina;
    private $conteo;
    private $limit;
    private $conn;

    function __construct() {
        $this->conn = new Database();
    }

    public function getProcesarSqlConteo($sql, $pg_siguiente = 0, $num_reg = 0, $limite = 0) {
        $this->offset = 0;
        $this->pagina = 1;
        if ($limite === 0) {
            $this->limit = GetLimitBrowser();
            if (!$this->limit)
                $this->limit = 20;
        }
        else {
            $this->limit = $limite;
        }

        if ($pg_siguiente) {
            $this->pagina = intval($pg_siguiente);
            if ($this->pagina > 1)
                $this->offset = ($this->pagina - 1) * ($this->limit);
        }

        if (!$num_reg) {
            // $resultado = $this->conn->prepare($sql);
            // $resultado->execute();
            // $this->conteo = $resultado->fetch(PDO::FETCH_COLUMN);
			$this->conteo = $this->conn->getFields($sql)->fields[0];

        } else {
            $this->conteo = $num_reg;
        }

        $response['offset'] = $this->offset;
        $response['pagina'] = $this->pagina;
        $response['conteo'] = $this->conteo;
        $response['limit'] = $this->limit;
        return $response;
    }

}

?>