@extends('layouts.error', [
    'errorCode' => '404',
    'messageIcon' => 'fa-search-location',
    'errorMessage' => 'Página ',
    'errorMessageHighlight' => 'não encontrada',
    'solutionMessage' => 'Verifique o ',
    'solutionMessageHighlight' => 'endereço informado'
]);
