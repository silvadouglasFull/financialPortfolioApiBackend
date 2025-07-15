<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use App\Models\User; // Certifique-se de que seu modelo User está aqui

class RouteMenuGenerator
{
    /**
     * Gera uma lista de itens de menu para rotas navegáveis.
     *
     * @return array
     */
    public function generate(): array
    {
        $menuItems = [];
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            // Filtra por rotas GET e que tenham um nome (geralmente indicam rotas navegáveis)
            // e que não sejam rotas de API, de autenticação ou internas do Laravel/AdminLTE.
            if (
                in_array('GET', $route->methods()) &&
                $route->getName() &&
                !$this->isExcludedRoute($route) &&
                !$this->hasRequiredParameters($route)
            ) {
                // Tenta criar um texto amigável para o menu
                $text = ucwords(str_replace(['.', '-', '_'], ' ', $route->getName()));

                // Adiciona o item ao menu
                $menuItems[] = [
                    'text' => $text,
                    'url'  => route($route->getName()), // Gera a URL da rota
                    'icon' => 'fas fa-link', // Ícone genérico
                    // 'can' => 'view-' . $route->getName(), // Exemplo: Se você tiver um sistema de permissões baseado no nome da rota
                ];
            }
        }

        // Você pode ordenar o menu aqui se desejar
        usort($menuItems, function ($a, $b) {
            return strcmp($a['text'], $b['text']);
        });

        return $menuItems;
    }

    /**
     * Verifica se a rota deve ser excluída do menu.
     *
     * @param \Illuminate\Routing\Route $route
     * @return bool
     */
    protected function isExcludedRoute(\Illuminate\Routing\Route $route): bool
    {
        $routeName = $route->getName();

        // Exclua rotas de API
        if (str_starts_with($routeName, 'api.')) {
            return true;
        }

        // Exclua rotas de autenticação (se já estiverem no menu padrão do AdminLTE ou não forem para navegação direta)
        if (str_starts_with($routeName, 'login') || str_starts_with($routeName, 'logout') || str_starts_with($routeName, 'register')) {
            return true;
        }
        if (str_starts_with($routeName, 'password.')) { // Esqueceu senha, resetar senha, etc.
            return true;
        }
        if (str_starts_with($routeName, 'verification.')) { // Verificação de e-mail
            return true;
        }

        // Exclua rotas internas do Laravel ou de pacotes
        if (str_starts_with($routeName, 'ignition.') || str_starts_with($routeName, 'sanctum.') || str_starts_with($routeName, 'livewire.')) {
            return true;
        }
        // Adicione outras rotas a serem excluídas (ex: AdminLTE interna, se você personalizou)
        if (str_starts_with($routeName, 'adminlte.')) {
            return true;
        }


        return false;
    }

    /**
     * Verifica se a rota possui parâmetros obrigatórios que não podem ser resolvidos automaticamente.
     *
     * @param \Illuminate\Routing\Route $route
     * @return bool
     */
    protected function hasRequiredParameters(\Illuminate\Routing\Route $route): bool
    {
        return !empty($route->parameterNames());
    }
}
