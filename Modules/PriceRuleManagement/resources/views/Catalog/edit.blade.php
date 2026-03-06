@extends('base::layouts.mt-main')

@section('content')
    <div class="card">
        <div class="card-title p-5">
            <h3>Edit Catalog Price Rule</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('catalog-price-rules.update', $priceRule->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <div data-repeater-list="kt_docs_repeater_basic">
                        <div data-repeater-item>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="form-label" for="name">Rule Name</label>
                                    <input type="text" class="form-control mb-2 mb-md-0" id="name" name="name"
                                           value="{{ old('name', $priceRule->name) }}" placeholder="Enter Rule Name"/>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="description">Description</label>
                                    <input type="text" class="form-control mb-2 mb-md-0" id="description"
                                           name="description"
                                           value="{{ old('description', $priceRule->description) }}"
                                           placeholder="Enter Description"/>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check form-check-custom form-check-solid mt-2 mt-md-11">
                                        <input class="form-check-input" type="checkbox" value="1" id="active"
                                               name="active"
                                            {{ $priceRule->is_active ? 'checked' : '' }}/>
                                        <label class="form-check-label" for="active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mt-10">
                                <div class="col-md-4">
                                    <label class="form-label" for="customer_groups">Customer Groups</label>
                                    <select class="form-select form-select-solid" name="customer_groups[]"
                                            id="customer_groups" multiple
                                            data-control="select2" data-placeholder="Select an option">
                                        <?php
                                        $customerGroupsOld = json_decode($priceRule->customer_groups, true) ?? [];
                                        ?>
                                        @foreach($customerGroups as $group)
                                            <option value="{{ $group->id }}"
                                                    @if(in_array($group->id, $customerGroupsOld ?? []))
                                                    selected
                                                @endif>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="priority">Priority</label>
                                    <input type="number" class="form-control mb-2 mb-md-0" id="priority" name="priority"
                                           value="{{ old('priority', $priceRule->priority) }}"
                                           placeholder="Enter Priority"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mt-10">
                        <div class="accordion" id="condition">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="condition_header">
                                    <button class="accordion-button fs-4 fw-semibold" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#conditions"
                                            aria-expanded="true" aria-controls="conditions">
                                        Conditions
                                    </button>
                                </h2>
                                <div id="conditions" class="accordion-collapse collapse"
                                     aria-labelledby="condition_header" data-bs-parent="#condition">
                                    <div class="accordion-body">
                                        <div class="form-group mt-10">
                                            <div id="conditions_list">
                                                <?php
                                                $conditions = json_decode($priceRule->conditions, true);
                                                ?>
                                                @foreach($conditions as $condition)
                                                    <div class="condition_item">
                                                        <div class="form-group row">
                                                            <div class="col-md-4">
                                                                <label class="form-label" for="rule_type">Rule
                                                                    Type</label>
                                                                <select class="form-select form-select-solid rule_type"
                                                                        name="rule_type[]">
                                                                    <option value="">Select Rule Type</option>
                                                                    @foreach(\Modules\PriceRuleManagement\Models\CatalogPriceRule::RULE_TYPE_LIST as $key => $type)
                                                                        <option value="{{ $key }}"
                                                                                @if($key == $condition['rule_type']) selected @endif>
                                                                            {{ $type }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 mt-3 rule_values_container">
                                                                <label class="form-label"></label>
                                                                @if($condition['rule_type'] == 'sku')
                                                                    <?php
                                                                    $skuValues = implode(',', $condition['rule_values']);
                                                                    ?>
                                                                    <label class="form-label"></label>
                                                                    <input type="text" class="form-control mb-2 mb-md-0"
                                                                           value="{{ $skuValues }}"
                                                                           name="rule_values[]">
                                                                @else
                                                                    <label
                                                                        class="form-label">{{ $condition['rule_type'] }}</label>
                                                                    <select
                                                                        class="form-select form-select-solid rule-values-select"
                                                                        name="rule_values[]" multiple
                                                                        data-control="select2"
                                                                        data-placeholder="Select an option">
                                                                        @foreach( $categories as $value)
                                                                            <option value="{{ $value->id }}"
                                                                                    @if(in_array($value->id, $condition['rule_values'])) selected @endif>
                                                                                {{ $value->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-2 mt-3">
                                                                <label class="form-label"></label>
                                                                <button type="button"
                                                                        class="form-control btn btn-danger remove-condition">
                                                                    Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="col-md-2 mt-3">
                                                <button type="button" class="form-control btn btn-primary mt-3"
                                                        id="add_condition">Add Condition
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mt-10">
                        <div class="accordion" id="action">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="action_header">
                                    <button class="accordion-button fs-4 fw-semibold" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#actions"
                                            aria-expanded="true" aria-controls="actions">
                                        Actions
                                    </button>
                                </h2>
                                <div id="actions" class="accordion-collapse collapse"
                                     aria-labelledby="action_header" data-bs-parent="#action">
                                    <div class="accordion-body">
                                        <div class="form-group mt-10">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <label class="form-label" for="discount_type">Apply</label>
                                                    <select class="form-select form-select-solid"
                                                            name="discount_type" id="discount_type">
                                                        @foreach(\Modules\PriceRuleManagement\Models\CatalogPriceRule::RULE_DISCOUNT_TYPE_LIST as $key => $type)
                                                            <option value="{{ $key }}"
                                                                    @if($key == $priceRule->discount_type) selected @endif>
                                                                {{ $type }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label" for="discount_amount">Discount
                                                        Amount</label>
                                                    <input type="text" class="form-control mb-2 mb-md-0"
                                                           id="discount_amount" name="discount_amount"
                                                           value="{{ old('discount_amount', $priceRule->discount_value) }}"
                                                           placeholder="Enter Description"/>
                                                </div>
                                                <div class="col-md-2">
                                                    <div
                                                        class="form-check form-check-custom form-check-solid mt-2 mt-md-11">
                                                        <input class="form-check-input" type="checkbox" value="1"
                                                               id="discard_subsequent" name="discard_subsequent"
                                                            {{ $priceRule->discard_subsequent ? 'checked' : '' }}/>
                                                        <label class="form-check-label" for="discard_subsequent">
                                                            Discard subsequent rules
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="saveCatalogPriceRule">Save</button>
            </form>
        </div>
    </div>
@stop

@section('custom-js-section')
    <script>
        $(document).ready(function () {
            // Function to initialize select2 for rule values
            function initializeSelect2(container) {
                container.find('.rule-values-select').select2({
                    placeholder: "Select an option",
                    allowClear: true
                });
            }

            // Handle rule type change to update conditions dynamically
            $(document).on('change', '.rule_type', function () {
                let ruleType = $(this).val();
                let container = $(this).closest('.condition_item').find('.rule_values_container');
                container.html('');

                if (ruleType === 'brand' || ruleType === 'category') {
                    $.ajax({
                        url: '{{ route('get.rule.values') }}',
                        type: 'GET',
                        data: {type: ruleType},
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (Array.isArray(response)) {
                                let options = response.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
                                container.html(`
                            <label class="form-label">${ruleType.charAt(0).toUpperCase() + ruleType.slice(1)}</label>
                            <select class="form-select form-select-solid rule-values-select" name="rule_values[]" multiple>
                                ${options}
                            </select>
                        `);
                                initializeSelect2(container);
                            } else {
                                container.html(`<p class="text-danger">Error loading data</p>`);
                            }
                        },
                        error: function () {
                            container.html(`<p class="text-danger">Failed to fetch data</p>`);
                        }
                    });
                } else if (ruleType === 'sku') {
                    container.html(`
                <label class="form-label">Enter SKU</label>
                <mark>Add comma separated values</mark>
                <input type="text" class="form-control" name="rule_values[]">
            `);
                } else {
                    container.html(`
                <label class="form-label"></label>
                <input type="text" class="form-control mb-2 mb-md-0" disabled placeholder="Condition will be available here">
            `);
                }
            });

            // Add new condition block
            $('#add_condition').click(function () {
                let newCondition = `
            <div class="condition_item">
                <div class="form-group row">
                    <div class="col-md-4">
                        <label class="form-label" for="rule_type">Rule Type</label>
                        <select class="form-select form-select-solid rule_type" name="rule_type[]">
                            <option value="">Select Rule Type</option>
                            @foreach(\Modules\PriceRuleManagement\Models\CatalogPriceRule::RULE_TYPE_LIST as $key => $type)
                <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                </select>
            </div>
            <div class="col-md-4 mt-3 rule_values_container">
                <label class="form-label"></label>
                <input type="text" class="form-control mb-2 mb-md-0" disabled placeholder="Condition will be available here">
            </div>
            <div class="col-md-2 mt-3">
                <label class="form-label"></label>
                <button type="button" class="form-control btn btn-danger remove-condition">Remove</button>
            </div>
        </div>
    </div>
`;
                $('#conditions_list').append(newCondition);
            });

            // Remove condition block
            $(document).on('click', '.remove-condition', function () {
                $(this).closest('.condition_item').remove();
            });

            // Save the catalog price rule (Update function)
            $('#saveCatalogPriceRule').click(function (e) {
                e.preventDefault();

                let formData = {
                    name: $('#name').val(),
                    description: $('#description').val(),
                    active: $('#active').is(':checked') ? 1 : 0,
                    customer_groups: $('#customer_groups').val(),
                    priority: $('#priority').val(),
                    rule_conditions: [],
                    discount_type: $('#discount_type').val(),
                    discount_amount: $('#discount_amount').val(),
                    discard_subsequent: $('#discard_subsequent').is(':checked') ? 1 : 0,
                    _token: '{{ csrf_token() }}',
                    id: '{{ $priceRule->id }}'
                };

                // Gather rule conditions
                $('.condition_item').each(function () {
                    let ruleType = $(this).find('.rule_type').val();
                    let ruleValues = $(this).find('[name="rule_values[]"]').val() || [];

                    if (ruleType) {
                        formData.rule_conditions.push({
                            rule_type: ruleType,
                            rule_values: Array.isArray(ruleValues) ? ruleValues : [ruleValues]
                        });
                    }
                });

                $.ajax({
                    url: '{{ route("catalog-price-rules.update") }}',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Success', response.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = 'Something went wrong!';
                        if (errors) {
                            errorMessage = Object.values(errors).join('<br>');
                        }
                        Swal.fire('Validation Error', errorMessage, 'error');
                    }
                });
            });
        });

    </script>
@stop
