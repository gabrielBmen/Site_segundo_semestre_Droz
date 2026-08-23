<aside class="admin-sidebar">
    <div class="admin-brand">
        <img src="/assets/imagens/logo.png" alt="DROZ Robótica">
        <div>
            <strong>DROZ Robótica</strong>
            <small>Administração</small>
        </div>
    </div>

    <nav class="admin-nav">
        <a class="<?= $adminPagina === 'Dashboard' ? 'active' : '' ?>" href="/admin/">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="/admin/produtos.php"><i class="bi bi-box-seam"></i> Produtos</a>
        <a href="/admin/categorias.php"><i class="bi bi-tags"></i> Categorias</a>
        <a href="/admin/pedidos.php"><i class="bi bi-receipt"></i> Pedidos</a>
        <a href="/admin/usuarios.php"><i class="bi bi-people"></i> Usuários</a>
        <a href="/admin/configuracoes.php"><i class="bi bi-gear"></i> Configurações</a>
    </nav>

    <div class="admin-help">
        <div class="small text-white-50">Atalhos</div>
        <a class="btn btn-sm btn-primary w-100 mt-2" href="/produtos.php">Ver site</a>
    </div>
</aside>
