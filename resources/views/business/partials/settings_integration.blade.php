<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-12">
            <h4>@lang('integration.integration_efi')</h4>
        </div>
    </div>
    <?php
    // dd($integrations);
    ?>
    <div class="row">
        <div class="col-sm-6">
            {{ Form::text('integrations[efi][id]', $integrations['efi']['id'] ?? '', [
                'class' => 'hidden',
            ]) }}
            <div class="form-group">
                {!! Form::label('integrations[efi][payee_code]', __('integration.payee_code') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {{ Form::text('integrations[efi][payee_code]', $integrations['efi']['payee_code'] ?? '', [
                        'class' => 'form-control',
                        'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                    ]) }}
                </div>
                <p>
                    <a href="https://dev.efipay.com.br/img/identificador.png"
                        target="_blank">
                        Como localizar meu @lang('integration.payee_code') <i class="fa fa-question-circle"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('integrations[efi][certificate]', __('integration.certificate') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {{ Form::file('integrations[efi][certificate]', [
                        'class' => 'form-control',
                        'accept' => 'application/x-pkcs12',
                    ]) }}
                </div>
                @if (isset($integrations['efi']['certificate']) && $integrations['efi']['certificate'] != '')
                    <p class="help-block" style="color: red !important;">
                        <i>
                            Já há um certificado presente. O certificado
                            será substituído somente se você optar por selecionar um novo.
                        </i>
                    </p>
                @endif
                <p>
                    <a href="https://dev.gerencianet.com.br/docs/api-pix-autenticacao-e-seguranca#section-gerando-um-certificado-p12"
                        target="_blank">
                        Como gerar um @lang('integration.certificate') <i class="fa fa-question-circle"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('integrations[efi][key_client_id]', __('integration.key_client_id') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {{ Form::text('integrations[efi][key_client_id]', $integrations['efi']['key_client_id'] ?? '', [
                        'class' => 'form-control',
                        'placeholder' => 'Client_Id_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                    ]) }}
                </div>
                <p>
                    <a href="https://dev.gerencianet.com.br/docs/api-pix-autenticacao-e-seguranca#section-criar-uma-aplica-o-ou-configurar-uma-j-existente"
                        target="_blank">
                        Como gerar um @lang('integration.key_client_id') <i class="fa fa-question-circle"></i>
                    </a>
                </p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('integrations[efi][key_client_secret]', __('integration.key_client_secret') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::text('integrations[efi][key_client_secret]', $integrations['efi']['key_client_secret'] ?? '', [
                        'class' => 'form-control',
                        'placeholder' => 'Client_Secret_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                    ]) !!}
                </div>
                <p>
                    <a href="https://dev.gerencianet.com.br/docs/api-pix-autenticacao-e-seguranca#section-criar-uma-aplica-o-ou-configurar-uma-j-existente"
                        target="_blank">
                        Como gerar um @lang('integration.key_client_secret') <i class="fa fa-question-circle"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
    <hr />
</div>
