"use strict";
const dinheiro = (valor) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(Number.isFinite(valor) ? valor : 0);
};
const numeroSeguro = (valor) => {
    const numero = Number(valor);
    return Number.isFinite(numero) ? numero : 0;
};
const atualizarMetricas = (itens) => {
    // Reduce principal da rubrica: faturamento global a partir do array bruto.
    const faturamentoTotal = itens.reduce((total, item) => {
        return total + numeroSeguro(item.quantidade) * numeroSeguro(item.preco_unitario);
    }, 0);
    // Reduce para consolidar quantidade global de itens.
    const itensVendidos = itens.reduce((total, item) => {
        return total + numeroSeguro(item.quantidade);
    }, 0);
    // Reduce para descobrir o total de cada pedido sem depender de valores pré-calculados pela UI.
    const totaisPorPedido = itens.reduce((acc, item) => {
        const chave = String(item.id_pedido);
        const valorItem = numeroSeguro(item.quantidade) * numeroSeguro(item.preco_unitario);
        acc[chave] = (acc[chave] ?? 0) + valorItem;
        return acc;
    }, {});
    const pedidos = Object.keys(totaisPorPedido).length;
    const ticketMedio = pedidos > 0 ? faturamentoTotal / pedidos : 0;
    document.getElementById('metricFaturamento').textContent = dinheiro(faturamentoTotal);
    document.getElementById('metricPedidos').textContent = String(pedidos);
    document.getElementById('metricItens').textContent = String(itensVendidos);
    document.getElementById('metricTicket').textContent = dinheiro(ticketMedio);
};
const renderStatus = (status) => {
    const tbody = document.getElementById('statusTableBody');
    if (!tbody)
        return;
    if (status.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-white-50">Nenhum dado registrado.</td></tr>';
        return;
    }
    tbody.innerHTML = status.map((item) => `
        <tr>
            <td><span class="badge text-bg-secondary">${item.status}</span></td>
            <td>${numeroSeguro(item.quantidade_pedidos)}</td>
            <td>${dinheiro(numeroSeguro(item.valor_total))}</td>
        </tr>
    `).join('');
};
const renderTopProdutos = (produtos) => {
    const container = document.getElementById('topProductsList');
    if (!container)
        return;
    if (produtos.length === 0) {
        container.innerHTML = '<div class="text-white-50">Nenhum dado registrado.</div>';
        return;
    }
    container.innerHTML = produtos.map((produto, index) => `
        <div class="admin-info-box d-flex justify-content-between align-items-center gap-3">
            <div>
                <div class="fw-semibold">${index + 1}. ${produto.produto}</div>
                <small class="text-white-50">${produto.categoria} · ${numeroSeguro(produto.quantidade_vendida)} itens</small>
            </div>
            <strong>${dinheiro(numeroSeguro(produto.faturamento))}</strong>
        </div>
    `).join('');
};
const renderRawData = (itens) => {
    const tbody = document.getElementById('rawDataTableBody');
    const count = document.getElementById('rawDataCount');
    if (!tbody || !count)
        return;
    count.textContent = String(itens.length);
    if (itens.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-white-50">Nenhum dado registrado.</td></tr>';
        return;
    }
    tbody.innerHTML = itens.slice(0, 20).map((item) => `
        <tr>
            <td>#${numeroSeguro(item.id_pedido)}</td>
            <td>${item.produto}</td>
            <td>${numeroSeguro(item.quantidade)}</td>
            <td>${dinheiro(numeroSeguro(item.preco_unitario))}</td>
            <td>${dinheiro(numeroSeguro(item.quantidade) * numeroSeguro(item.preco_unitario))}</td>
            <td><span class="badge text-bg-secondary">${item.status}</span></td>
        </tr>
    `).join('');
};
const mostrarAlerta = (texto, tipo) => {
    const alert = document.getElementById('dashboardAlert');
    if (!alert)
        return;
    alert.textContent = texto;
    alert.className = `alert alert-${tipo}`;
};
document.addEventListener('DOMContentLoaded', async () => {
    if (!window.drozApi)
        return;
    try {
        const resultado = await window.drozApi.getJson('/api/dashboard.php');
        if (!resultado.sucesso || !resultado.dados) {
            throw new Error(resultado.mensagem ?? 'Não foi possível carregar a dashboard.');
        }
        const dados = resultado.dados;
        const itens = Array.isArray(dados.itens) ? dados.itens : [];
        const status = Array.isArray(dados.status) ? dados.status : [];
        const topProdutos = Array.isArray(dados.top_produtos) ? dados.top_produtos : [];
        atualizarMetricas(itens);
        renderStatus(status);
        renderTopProdutos(topProdutos);
        renderRawData(itens);
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            emptyState.classList.toggle('d-none', itens.length > 0);
        }
    }
    catch (erro) {
        console.error('Erro na dashboard:', erro);
        mostrarAlerta(erro instanceof Error ? erro.message : 'Não foi possível carregar os dados da dashboard.', 'danger');
        renderStatus([]);
        renderTopProdutos([]);
        renderRawData([]);
        atualizarMetricas([]);
    }
});
//# sourceMappingURL=dashboard.js.map