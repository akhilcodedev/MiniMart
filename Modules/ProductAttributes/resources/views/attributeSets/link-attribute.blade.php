<input type="hidden" name="attribute_set_edit_id" id="attribute_set_edit_id" value="{{ $attributeSetData->id }}" />
<input type="hidden" name="attribute_edit_id" id="attribute_edit_id" value="{{ $activeAttribute->id }}" />

@if(in_array($activeAttribute->input_type, array_keys($attributeValueTypes)))
<div class="row">
    <div class="col col-12">
        <label for="set_edit_value" class="form-label">{{ __('Value') }}</label>
        <textarea rows="6" cols="30" name="set_edit_value" id="set_edit_value" >{{ $mappedSetObj->value }}</textarea>
    </div>
</div>
@else
    <input type="hidden" name="set_edit_value" id="set_edit_value" value="{{ $mappedSetObj->value }}" />
@endif

<div class="row">
    <div class="col col-12">
        <label for="set_edit_description" class="form-label">{{ __('Description') }}</label>
        <textarea rows="6" cols="30" name="set_edit_description" id="set_edit_description" >{{ $mappedSetObj->description }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col col-12">
        <label for="set_edit_sort_order" class="form-label">{{ __('Sort Order') }}</label>
        <input name="set_edit_sort_order" id="set_edit_sort_order" type="number" value="{{ $mappedSetObj->sort_order }}">
    </div>
</div>

<div class="row">
    <div class="col col-12">
        <label for="set_edit_is_required" class="form-label">{{ __('Required') }}</label>
        <select name="set_edit_is_required" id="set_edit_is_required" class="form-control form-control-solid">
            @foreach($attributeSetMapRequires as $attributeStatusKey => $attributeStatusEl)
                <option value="{{ $attributeStatusKey }}" @if($mappedSetObj->is_required == $attributeStatusKey) selected @endif>{{ $attributeStatusEl }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col col-12">
        <label for="set_edit_is_active" class="form-label">{{ __('Status') }}</label>
        <select name="set_edit_is_active" id="set_edit_is_active" class="form-control form-control-solid">
            @foreach($attributeSetMapStatuses as $attributeStatusKey => $attributeStatusEl)
                <option value="{{ $attributeStatusKey }}" @if($mappedSetObj->is_active == $attributeStatusKey) selected @endif>{{ $attributeStatusEl }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col col-12">
        <button type="button" class="btn btn-primary edit-attribute-modal-submit-btn">
            <i class="fas fa-save"></i> {{ __('Edit Attribute') }}
        </button>
    </div>
</div>
