<?php
namespace Src\Services;

class ToastService {
    public function success($message) {
        $_SESSION['toast'] = ['type' => 'success', 'message' => $message];
    }

    public function error($message) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => $message];
    }

    public function clear() {
        unset($_SESSION['toast']);
    }
}
