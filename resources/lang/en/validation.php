<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute deve ser aceito.',
    'active_url' => ':attribute não é um URL válido.',
    'after' => ':attribute deve ser uma data depois :date.',
    'after_or_equal' => ':attribute deve ser uma data posterior ou igual a :date.',
    'alpha' => ':attribute pode conter apenas letras.',
    'alpha_dash' => ':attribute só pode conter letras, números, travessões e sublinhados.',
    'alpha_num' => ':attribute pode conter apenas letras e números.',
    'array' => ':attribute deve ser um array.',
    'before' => ':attribute deve ser uma data antes:date.',
    'before_or_equal' => ':attribute deve ser uma data anterior ou igual a :date.',
    'between' => [
        'numeric' => ':attribute deve estar entre :min e :max.',
        'file' => ':attribute deve estar entre :min e :max kilobytes.',
        'string' => ':attribute deve estar entre :min e :max caracteres.',
        'array' => ':attribute deve estar entre :min e :max itens.',
    ],
    'boolean' => ':attribute campo deve ser verdadeiro ou falso.',
    'confirmed' => ':attribute a confirmação não corresponde.',
    'date' => ':attribute não é uma data válida.',
    'date_equals' => ':attribute deve ser uma data igual a :date.',
    'date_format' => ':attribute não corresponde ao formato :format.',
    'different' => ':attribute e :other deve ser diferente.',
    'digits' => ':attribute deve ser :digits digitos.',
    'digits_between' => ':attribute deve estar entre :min e :max digitos.',
    'dimensions' => ':attribute tem dimensões de imagem inválidas.',
    'distinct' => ':attribute campo tem um valor duplicado.',
    'email' => ':attribute Deve ser um endereço de e-mail válido.',
    'ends_with' => ':attribute must end with one of the following: :values',
    'exists' => ':attribute é inválido(a) .',
    'file' => ':attribute deve ser um arquivo.',
    'filled' => ':attribute campo deve ter um valor.',
    'gt' => [
        'numeric' => ':attribute deve ser maior que :value.',
        'file' => ':attribute deve ser maior que :value kilobytes.',
        'string' => ':attribute deve ser maior que :value characters.',
        'array' => ':attribute deve ser maior que:value items.',
    ],
    'gte' => [
        'numeric' => ':attribute deve ser maior ou igual :value.',
        'file' => ':attribute deve ser maior ou igual :value kilobytes.',
        'string' => ':attribute deve ser maior ou igual :value caracteres.',
        'array' => ':attribute deve ter :value itens ou mais.',
    ],
    'image' => ':attribute deve ser uma imagem.',
    'in' => ':attribute é inválido(a).',
    'in_array' => ':attribute campo não existe em :other.',
    'integer' => ':attribute deve ser inteiro.',
    'ip' => ':attribute deve ser um endereço de IP.',
    'ipv4' => ':attribute deve ser válido IPv4 endereço.',
    'ipv6' => ':attribute deve ser válido IPv6 endereço.',
    'json' => ':attribute deve ser válido JSON string.',
    'lt' => [
        'numeric' => ':attribute deve ser menor que :value.',
        'file' => ':attribute deve ser menor que :value kilobytes.',
        'string' => ':attribute deve ser menor que :value caracteres.',
        'array' => ':attribute deve ser menor que :value itens.',
    ],
    'lte' => [
        'numeric' => ':attribute deve ser menor ou igual :value.',
        'file' => ':attribute deve ser menor ou igual :value kilobytes.',
        'string' => ':attribute deve ser menor ou igual :value caracteres.',
        'array' => ':attribute não deve ter mais que :value itens.',
    ],
    'max' => [
        'numeric' => ':attribute não pode ser maior que :max.',
        'file' => ':attribute não pode ser maior que :max kilobytes.',
        'string' => ':attribute não pode ser maior que :max caracteres.',
        'array' => ':attribute não pode ser maior que :max itens.',
    ],
    'mimes' => ':attribute deve ser um arquivo do tipo: :values.',
    'mimetypes' => ':attribute deve ser um arquivo do tipo: :values.',
    'min' => [
        'numeric' => ':attribute deve ter pelo menos :min.',
        'file' => ':attribute deve ter pelo menos :min kilobytes.',
        'string' => ':attribute deve ter pelo menos :min caracteres.',
        'array' => ':attribute deve ter pelo menos :min itens.',
    ],
    'not_in' => ':attribute é inválido.',
    'not_regex' => ':attribute format é inválido.',
    'numeric' => ':attribute must be a number.',
    'present' => ':attribute campo deve estar presente.',
    'regex' => ':attribute format é inválido.',
    'required' => ':attribute é obrigatório.',
    'required_if' => ':attribute campo é obrigatório quando:other é :value.',
    'required_unless' => ':attribute campo é obrigatório a menos :other is in :values.',
    'required_with' => ':attribute campo é obrigatório quando:values é presente.',
    'required_with_all' => ':attribute campo é obrigatório quando :values estão presentes.',
    'required_without' => ':attribute campo é obrigatório quando :values não está presente.',
    'required_without_all' => ':attribute campo é obrigatório quando nenhum :values estiver presente.',
    'same' => ':attribute e :other deve combinar.',
    'size' => [
        'numeric' => ':attribute deve conter :size.',
        'file' => ':attribute deve conter :size kilobytes.',
        'string' => ':attribute deve conter :size caracteres.',
        'array' => ':attribute deve conter :size itens.',
    ],
    'starts_with' => ':attribute must start with one of the following: :values',
    'string' => ':attribute deve ser uma string.',
    'timezone' => ':attribute deve ser uma zona válida.',
    'unique' => ':attribute já foi gerado(a).',
    'uploaded' => ':attribute falha para upload.',
    'url' => ':attribute inválido.',
    'uuid' => 'O :attribute deve ser um válido',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'mensagem personalizada',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
    'custom-messages' => [
        'quantity_not_available' => 'Somente :qty :unit disponiveis',
        'this_field_is_required' => 'Este campo é obrigatório'
    ],

];