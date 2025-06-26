@if($row->status == 'draft')
<span class="badge badge-light-secondary">Draft</span>
@elseif($row->status == 'calculated')
<span class="badge badge-light-info">Sudah Dihitung</span>
@elseif($row->status == 'approved')
<span class="badge badge-light-success">Disetujui</span>
@elseif($row->status == 'paid')
<span class="badge badge-light-primary">Sudah Dibayar</span>
@endif
