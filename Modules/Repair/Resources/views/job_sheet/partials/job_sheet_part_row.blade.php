<tr class="product_row">
    <td class="text-uppercase flex items-center">
        <input type="number" title="@lang('sale.qty')" class="hidden" value="{{ (float) $product->id }}"
            name="parts[{{ $variation_id }}][product_id]" />
        {{ $product->name }}
    </td>
    <td>
        <div class="input-group">
            <input type="number" required data-id="quantity" step="0.01" title="@lang('sale.qty')"
                class="form-control text-right" value="{{ (float) $quantity }}" trigger="updateTotal"
                name="parts[{{ $variation_id }}][quantity]" />
            <div class="input-group-addon">
                {{ $product->unit->short_name }}
            </div>
        </div>
    </td>
    <td>
        <input type="number" required data-id="unit_price" step="0.01" title="@lang('sale.unit_price')"
            class="form-control text-right" value="{{ @number_format((float) $unit_price, 2) }}"
            name="parts[{{ $variation_id }}][unit_price]" />
    </td>
    <td>
        <input type="number" data-id="total_amount" disabled step="0.01" title="@lang('sale.total_amount')"
            class="form-control text-right" name="total_amount"
            value="{{ @number_format((float) $unit_price * (float) $quantity, 2) }}" />
    </td>
    <td class="text-center">
        <button type="button" name="remove_product_row" title="Remover Item" class="btn btn-danger remove_product_row">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
    </td>
</tr>
