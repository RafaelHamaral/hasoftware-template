<?php

class ConfigModel
{
    public function getEmailTemplate()
    {
        return [
            'assunto' => 'Contato recebido',
            'mensagem' => 'Olá {{nome}}, recebemos seu contato.'
        ];
    }

    public function getContato()
    {
        return [
            'email' => 'contato@empresa.com',
            'telefone' => '(11) 99999-9999'
        ];
    }

    public function getUsuarios()
    {
        return [
            ['name' => 'Admin', 'email' => 'admin@email.com', 'type' => 'admin'],
            ['name' => 'Viewer', 'email' => 'viewer@email.com', 'type' => 'viewer']
        ];
    }
}