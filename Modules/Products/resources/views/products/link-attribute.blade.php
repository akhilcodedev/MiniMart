@foreach($mappedAttributes as $attribute)
    <div class="mb-10 fv-row">
        <label class="required form-label" for="attribute_link_{{ $attribute['attributeId'] }}">
            {{ $attribute['attributeLabel'] }}
        </label>

        @if($attribute['attributeInputType'] === 'select')
            <select name="attribute_link[{{ $attribute['attributeId'] }}]"
                    id="attribute_link_{{ $attribute['attributeId'] }}"
                    class="form-control mb-2">
                <option value="">Select {{ $attribute['attributeLabel'] }}</option>
                @foreach($attribute['attributeValue'] as $option)
                    <option value="{{ $option['id'] }}"
                            @if($option['id'] == $attribute['attributeSelectedValue']) selected @endif>
                        {{ $option['value'] }}
                    </option>
                @endforeach
            </select>

        @elseif($attribute['attributeInputType'] === 'date')
{{--            <input type="text"--}}
{{--                   class="form-control attribute-link-date"--}}
{{--                   placeholder="{{ $attribute['attributeLabel'] }}"--}}
{{--                   name="attribute_link[{{ $attribute['attributeId'] }}]"--}}
{{--                   id="attribute_link_{{ $attribute['attributeId'] }}"--}}
{{--                   value="{{ $attribute['attributeSelectedValue'] ?? '' }}"/>--}}
            <input type="date"
                   class="form-control attribute-link-date"
                   placeholder="{{ $attribute['attributeLabel'] }}"
                   name="attribute_link[{{ $attribute['attributeId'] }}]"
                   id="attribute_link_{{ $attribute['attributeId'] }}"
                   value="{{ old('attribute_link.'.$attribute['attributeId'], $attribute['attributeSelectedValue'] ?? '') }}"/>

        @else
            <input type="text"
                   class="form-control"
                   placeholder="{{ $attribute['attributeLabel'] }}"
                   name="attribute_link[{{ $attribute['attributeId'] }}]"
                   id="attribute_link_{{ $attribute['attributeId'] }}"
                   value="{{ $attribute['attributeSelectedValue'] ?? '' }}"/>
        @endif

        <div class="text-muted fs-7">{{ $attribute['attributeLabel'] }}</div>
    </div>
@endforeach
