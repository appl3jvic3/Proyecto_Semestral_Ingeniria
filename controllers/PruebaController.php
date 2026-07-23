<?php
class PruebaController
{
    public function index()
    {
        // Probar la función view directamente
        view('prueba', ['mensaje' => 'Esto es una prueba'], 'list');
    }
}
