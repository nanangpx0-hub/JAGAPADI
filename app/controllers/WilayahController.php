<?php
class WilayahController extends Controller {
    private $kabModel;
    private $kecModel;
    private $desaModel;
    public function __construct() {
        $this->kabModel = $this->model('MasterKabupaten');
        $this->kecModel = $this->model('MasterKecamatan');
        $this->desaModel = $this->model('MasterDesa');
    }
    public function kabupaten() {
        $q = $_GET['q'] ?? null;
        $limit = $_GET['limit'] ?? 100;
        $data = $q ? $this->kabModel->search($q, $limit) : $this->kabModel->getAllOrdered();
        $this->json(['status' => 'success', 'data' => $data]);
    }
    public function kecamatan($kabupatenId = null) {
        $kabupatenId = $kabupatenId ?? ($_GET['kabupaten_id'] ?? null);
        if (!$kabupatenId) $this->json(['status' => 'error', 'message' => 'kabupaten_id required'], 400);
        $q = $_GET['q'] ?? null;
        $limit = $_GET['limit'] ?? 100;
        $data = $this->kecModel->getByKabupaten($kabupatenId, $q, $limit);
        $this->json(['status' => 'success', 'data' => $data]);
    }
    public function desa($kecamatanId = null) {
        $kecamatanId = $kecamatanId ?? ($_GET['kecamatan_id'] ?? null);
        if (!$kecamatanId) $this->json(['status' => 'error', 'message' => 'kecamatan_id required'], 400);
        $q = $_GET['q'] ?? null;
        $limit = $_GET['limit'] ?? 200;
        $data = $this->desaModel->getByKecamatan($kecamatanId, $q, $limit);
        $this->json(['status' => 'success', 'data' => $data]);
    }
}