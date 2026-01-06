<?php

class Database {
    private $config_file = __DIR__ . '/../data/cms_data/config.json';
    private $depoimentos_video_file = __DIR__ . '/../data/cms_data/depoimentos_video.json';
    private $depoimentos_texto_file = __DIR__ . '/../data/cms_data/depoimentos_texto.json';
    private $media_file = __DIR__ . '/../data/cms_data/media.json';

    public function __construct() {
        // Inicializa os arquivos JSON se não existirem
        if (!file_exists($this->config_file)) {
            $this->saveConfig([]);
        }
        if (!file_exists($this->depoimentos_video_file)) {
            $this->saveDepoimentosVideo([]);
        }
        if (!file_exists($this->depoimentos_texto_file)) {
            $this->saveDepoimentosTexto([]);
        }
        if (!file_exists($this->media_file)) {
            $this->saveMedia([]);
        }
    }

    // --- Configurações (Textos) ---

    private function loadConfig() {
        return json_decode(file_get_contents($this->config_file), true);
    }

    private function saveConfig($config) {
        file_put_contents($this->config_file, json_encode($config, JSON_PRETTY_PRINT));
    }

    public function getConfig($key, $default = null) {
        $config = $this->loadConfig();
        return $config[$key] ?? $default;
    }

    public function setConfig($key, $value) {
        $config = $this->loadConfig();
        $config[$key] = $value;
        $this->saveConfig($config);
    }

    public function getAllConfig() {
        return $this->loadConfig();
    }

    // --- Mídias (Imagens/Vídeos Principais) ---

    private function loadMedia() {
        return json_decode(file_get_contents($this->media_file), true);
    }

    private function saveMedia($media) {
        file_put_contents($this->media_file, json_encode($media, JSON_PRETTY_PRINT));
    }

    public function getImage($key) {
        $media = $this->loadMedia();
        return $media[$key] ?? null;
    }

    public function getVideo($key) {
        $media = $this->loadMedia();
        return $media[$key] ?? null;
    }

    public function setMedia($key, $caminho, $tipo) {
        $media = $this->loadMedia();
        $media[$key] = ['caminho' => $caminho, 'tipo' => $tipo];
        $this->saveMedia($media);
    }

    // --- Depoimentos em Vídeo ---

    private function loadDepoimentosVideo() {
        return json_decode(file_get_contents($this->depoimentos_video_file), true);
    }

    private function saveDepoimentosVideo($depoimentos) {
        file_put_contents($this->depoimentos_video_file, json_encode($depoimentos, JSON_PRETTY_PRINT));
    }

    public function getDepoimentosVideo($active_only = false) {
        $depoimentos = $this->loadDepoimentosVideo();
        if ($active_only) {
            return array_filter($depoimentos, fn($dep) => $dep['ativo'] ?? true);
        }
        return $depoimentos;
    }

    public function addDepoimentoVideo($data) {
        $depoimentos = $this->loadDepoimentosVideo();
        $data['id'] = time() . uniqid();
        $data['ativo'] = true;
        $depoimentos[] = $data;
        $this->saveDepoimentosVideo($depoimentos);
    }

    public function updateDepoimentoVideo($id, $data) {
        $depoimentos = $this->loadDepoimentosVideo();
        foreach ($depoimentos as $key => $dep) {
            if ($dep['id'] === $id) {
                $depoimentos[$key] = array_merge($dep, $data);
                $this->saveDepoimentosVideo($depoimentos);
                return true;
            }
        }
        return false;
    }

    public function deleteDepoimentoVideo($id) {
        $depoimentos = $this->loadDepoimentosVideo();
        $depoimentos = array_filter($depoimentos, fn($dep) => $dep['id'] !== $id);
        $this->saveDepoimentosVideo(array_values($depoimentos));
    }

    // --- Depoimentos em Texto ---

    private function loadDepoimentosTexto() {
        return json_decode(file_get_contents($this->depoimentos_texto_file), true);
    }

    private function saveDepoimentosTexto($depoimentos) {
        file_put_contents($this->depoimentos_texto_file, json_encode($depoimentos, JSON_PRETTY_PRINT));
    }

    public function getDepoimentosTexto($active_only = false) {
        $depoimentos = $this->loadDepoimentosTexto();
        if ($active_only) {
            return array_filter($depoimentos, fn($dep) => $dep['ativo'] ?? true);
        }
        return $depoimentos;
    }

    public function addDepoimentoTexto($data) {
        $depoimentos = $this->loadDepoimentosTexto();
        $data['id'] = time() . uniqid();
        $data['ativo'] = true;
        $depoimentos[] = $data;
        $this->saveDepoimentosTexto($depoimentos);
    }

    public function updateDepoimentoTexto($id, $data) {
        $depoimentos = $this->loadDepoimentosTexto();
        foreach ($depoimentos as $key => $dep) {
            if ($dep['id'] === $id) {
                $depoimentos[$key] = array_merge($dep, $data);
                $this->saveDepoimentosTexto($depoimentos);
                return true;
            }
        }
        return false;
    }

    public function deleteDepoimentoTexto($id) {
        $depoimentos = $this->loadDepoimentosTexto();
        $depoimentos = array_filter($depoimentos, fn($dep) => $dep['id'] !== $id);
        $this->saveDepoimentosTexto(array_values($depoimentos));
    }
}
