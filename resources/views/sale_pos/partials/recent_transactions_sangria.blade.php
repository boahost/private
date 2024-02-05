
@if(!empty($data))
<table class="table table-slim no-border">
	@foreach ($data as $item)
	<tr class="cursor-pointer" 
	data-toggle="tooltip"
	data-html="true" >
	<td>
		{{ $loop->iteration}}.
	</td>
	<td>
		{{ $item->type == 'sangria' ? 'Sangria' : 'Suprimento' }}
	</td>
	<td class="display_currency">
		{{ $item->value }}
	</td>

	<td>
		{{ $item->note}}
	</td>
	<td>

		<a href="{{action('CashRegisterController@sangriaSuprimentoDestroy', [$item->id])}}" class="delete-sale" style="padding-left: 20px; padding-right: 20px"><i class="fa fa-trash text-danger" title="{{__('lang_v1.click_to_delete')}}"></i></a>


	</td>
</tr>
@endforeach
</table>
@else
<p>Nada encontrado!</p>
@endif