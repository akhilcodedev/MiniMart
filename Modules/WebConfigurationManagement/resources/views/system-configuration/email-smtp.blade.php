@extends('webconfigurationmanagement::configurations.core-config')

@section('container')
    <form method="POST" class="h-100" action="{{route('core.config.update')}}">
        @csrf
        <div class="form-group">
            <div data-repeater-list="data">
                <div data-repeater-item>
                    <div class="fv-row form-group row mb-5">
                        <div class="col-md-4 mb-4">
                            <label class="form-label" for="mail_type">Mail Type</label>
                            <input type="text" class="form-control mb-2 mb-md-0" id="mail_type" name="mail_type" value="{{ getConfigData('mail_type', 'webconfigurations_') ?? '' }}"/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_driver">Mail Driver</label>
                            <input type="text" class="form-control mb-2 mb-md-0" id="mail_driver" name="mail_driver" value="{{ getConfigData('mail_driver', 'webconfigurations_') ?? '' }}"/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_driver">Mail Host</label>
                            <input type="text" class="form-control mb-2 mb-md-0" id="mail_driver" name="mail_host" value="{{ getConfigData('mail_host', 'webconfigurations_') ?? '' }}" />
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label" for="mail_driver">Mail Port</label>
                            <input type="text" class="form-control mb-2 mb-md-0" id="mail_driver" name="mail_port" value="{{ getConfigData('mail_port', 'webconfigurations_') ?? '' }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_driver">Mail Username</label>
                            <input type="text" class="form-control mb-2 mb-md-0" id="mail_driver" name="mail_username" value="{{ getConfigData('mail_username', 'webconfigurations_') ?? '' }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_driver">Mail Password</label>
                            <input type="password" class="form-control mb-2 mb-md-0" id="mail_driver" name="mail_password" value="{{ getConfigData('mail_password', 'webconfigurations_') ?? '' }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_driver">Mail Encryption</label>
                            <input type="text" class="form-control mb-2 mb-md-0" id="mail_driver" name="mail_encryption" value="{{ getConfigData('mail_encryption', 'webconfigurations_') ?? '' }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mail_driver">Mail From</label>
                            <input type="email" class="form-control mb-2 mb-md-0" id="mail_driver" name="mail_from" value="{{ getConfigData('mail_from', 'webconfigurations_') ?? '' }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
@endsection
