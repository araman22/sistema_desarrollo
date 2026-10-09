@if ($paginator->hasPages())
    <div class="row" style="justify-content: space-between; margin-top: 20px;">
        <div class="small muted">Mostrando {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }}</div>
        <div>{{ $paginator->links() }}</div>
    </div>
@endif
