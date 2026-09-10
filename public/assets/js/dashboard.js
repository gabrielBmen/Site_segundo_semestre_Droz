"use strict";
let paginaAtual = 1;
const dinheiro = (valor) => new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
}).format(Number.isFinite(valor) ? valor : 0);
const numeroSeguro = (valor) => Number.isFinite(valor) ? valor : 0;
const escaparHtml = (texto) => texto
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
const definirTexto = (id, texto) => {
    const elemento = document.getElementById(id);
    if (elemento)
        elemento.textContent = texto;
};
const valorSelect = (id, padrao) => {
    const elemento = document.getElementById(id);
    return elemento instanceof HTMLSelectElement ? elemento.value : padrao;
};
const valorInput = (id) => {
    const elemento = document.getElementById(id);
    return elemento instanceof HTMLInputElement ? elemento.value : '';
};
const atualizarMetricas = (itens) => {
    const vendas = itens.filter((item) => item.status !== 'cancelado');
    const faturamentoTotal = vendas.reduce((total, item) => {
        return total + numeroSeguro(item.quantidade) * numeroSeguro(item.preco_unitario);
    }, 0);
    const itensVendidos = vendas.reduce((total, item) => {
        return total + numeroSeguro(item.quantidade);
    }, 0);
    const totaisPorPedido = vendas.reduce((acumulador, item) => {
        const chave = String(item.id_pedido);
        const valorItem = numeroSeguro(item.quantidade) * numeroSeguro(item.preco_unitario);
        acumulador[chave] = (acumulador[chave] ?? 0) + valorItem;
        return acumulador;
    }, {});
    const pedidos = Object.keys(totaisPorPedido).length;
    const mesesComVenda = new Set(vendas.map((item) => item.data_pedido.slice(0, 7))).size;
    const faturamentoMedioMensal = faturamentoTotal / Math.max(1, mesesComVenda);
    definirTexto('metricFaturamento', dinheiro(faturamentoTotal));
    definirTexto('metricPedidos', String(pedidos));
    definirTexto('metricItens', String(itensVendidos));
    definirTexto('metricTicket', dinheiro(faturamentoMedioMensal));
};
const formatarData = (data) => {
    const [ano, mes, dia] = data.slice(0, 10).split('-');
    return ano && mes && dia ? `${dia}/${mes}/${ano}` : data;
};
const atualizarCamposPersonalizados = () => {
    const mostrar = valorSelect('periodoDashboard', 'mes') === 'personalizado';
    document.querySelectorAll('.dashboard-custom-date').forEach((campo) => {
        campo.classList.toggle('d-none', !mostrar);
    });
};
const criarUrlDashboard = () => {
    const periodo = valorSelect('periodoDashboard', 'mes');
    const params = new URLSearchParams({
        periodo,
        busca: valorInput('buscaDashboard').trim(),
        status: valorSelect('statusDashboard', ''),
        pagina: String(paginaAtual),
        por_pagina: valorSelect('porPaginaDashboard', '10')
    });
    if (periodo === 'personalizado') {
        params.set('inicio', valorInput('dataInicioDashboard'));
        params.set('fim', valorInput('dataFimDashboard'));
    }
    return `/api/dashboard.php?${params.toString()}`;
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
            <td><span class="badge text-bg-secondary">${escaparHtml(item.status)}</span></td>
            <td>${item.quantidade_pedidos}</td>
            <td>${dinheiro(item.valor_total)}</td>
        </tr>
    `).join('');
};
const renderTopProdutos = (produtos) => {
    const container = document.getElementById('topProductsList');
    if (!container)
        return;
    const topTresProdutos = produtos.slice(0, 3);
    if (topTresProdutos.length === 0) {
        container.innerHTML = '<div class="text-white-50">Nenhum dado registrado.</div>';
        return;
    }
    container.innerHTML = topTresProdutos.map((produto, indice) => `
        <div class="admin-info-box d-flex justify-content-between align-items-center gap-3">
            <div>
                <div class="fw-semibold">${indice + 1}. ${escaparHtml(produto.produto)}</div>
                <small class="text-white-50">${escaparHtml(produto.categoria)} · ${produto.quantidade_vendida} itens</small>
            </div>
            <strong>${dinheiro(produto.faturamento)}</strong>
        </div>
    `).join('');
};
const renderRawData = (itens, paginacao) => {
    const tbody = document.getElementById('rawDataTableBody');
    if (!tbody)
        return;
    definirTexto('rawDataCount', String(paginacao.total));
    definirTexto('dashboardPaginationLabel', `Página ${paginacao.pagina} de ${paginacao.total_paginas} · ${paginacao.total} item(ns)`);
    const anterior = document.getElementById('dashboardPreviousPage');
    const proxima = document.getElementById('dashboardNextPage');
    if (anterior instanceof HTMLButtonElement)
        anterior.disabled = paginacao.pagina <= 1;
    if (proxima instanceof HTMLButtonElement)
        proxima.disabled = paginacao.pagina >= paginacao.total_paginas;
    if (itens.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-white-50">Nenhum dado registrado.</td></tr>';
        return;
    }
    tbody.innerHTML = itens.map((item) => `
        <tr>
            <td>#${item.id_pedido}</td>
            <td>${escaparHtml(item.produto)}</td>
            <td>${item.quantidade}</td>
            <td>${dinheiro(item.preco_unitario)}</td>
            <td>${dinheiro(item.valor_item)}</td>
            <td><span class="badge text-bg-secondary">${escaparHtml(item.status)}</span></td>
        </tr>
    `).join('');
};
const mostrarAlerta = (texto, tipo) => {
    const alerta = document.getElementById('dashboardAlert');
    if (!alerta)
        return;
    alerta.textContent = texto;
    alerta.className = `alert alert-${tipo}`;
};
const ocultarAlerta = () => {
    const alerta = document.getElementById('dashboardAlert');
    if (alerta)
        alerta.className = 'alert d-none';
};
const paginacaoVazia = () => ({
    pagina: 1,
    por_pagina: 10,
    total: 0,
    total_paginas: 1
});
const carregarDashboard = async () => {
    if (!window.drozApi)
        return;
    try {
        const resultado = await window.drozApi.getJson(criarUrlDashboard());
        if (!resultado.sucesso || !resultado.dados) {
            throw new Error(resultado.mensagem ?? 'Não foi possível carregar a dashboard.');
        }
        const dados = resultado.dados;
        paginaAtual = dados.paginacao.pagina;
        atualizarMetricas(dados.itens);
        renderStatus(dados.status);
        renderTopProdutos(dados.top_produtos);
        renderRawData(dados.itens_paginados, dados.paginacao);
        definirTexto('dashboardPeriodLabel', `Exibindo vendas de ${formatarData(dados.periodo.inicio)} até ${formatarData(dados.periodo.fim)}.`);
        const vazio = document.getElementById('emptyState');
        if (vazio)
            vazio.classList.toggle('d-none', !dados.vazio);
        ocultarAlerta();
    }
    catch (erro) {
        console.error('Erro na dashboard:', erro);
        mostrarAlerta(erro instanceof Error ? erro.message : 'Não foi possível carregar os dados.', 'danger');
        atualizarMetricas([]);
        renderStatus([]);
        renderTopProdutos([]);
        renderRawData([], paginacaoVazia());
    }
};
document.addEventListener('DOMContentLoaded', () => {
    const periodo = document.getElementById('periodoDashboard');
    const inicio = document.getElementById('dataInicioDashboard');
    const fim = document.getElementById('dataFimDashboard');
    const hoje = new Date().toISOString().slice(0, 10);
    const trintaDiasAtras = new Date(Date.now() - 29 * 86400000).toISOString().slice(0, 10);
    if (inicio instanceof HTMLInputElement)
        inicio.value = trintaDiasAtras;
    if (fim instanceof HTMLInputElement)
        fim.value = hoje;
    atualizarCamposPersonalizados();
    periodo?.addEventListener('change', atualizarCamposPersonalizados);
    document.getElementById('dashboardFilters')?.addEventListener('submit', (evento) => {
        evento.preventDefault();
        paginaAtual = 1;
        void carregarDashboard();
    });
    document.getElementById('dashboardPreviousPage')?.addEventListener('click', () => {
        paginaAtual = Math.max(1, paginaAtual - 1);
        void carregarDashboard();
    });
    document.getElementById('dashboardNextPage')?.addEventListener('click', () => {
        paginaAtual += 1;
        void carregarDashboard();
    });
    void carregarDashboard();
});
//# sourceMappingURL=dashboard.js.map