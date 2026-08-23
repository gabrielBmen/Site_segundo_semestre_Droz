"use strict";
const parseResponse = async (response) => {
    const contentType = response.headers.get('content-type') ?? '';
    if (!contentType.includes('application/json')) {
        throw new Error(`Resposta inválida do servidor (HTTP ${response.status}).`);
    }
    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.mensagem ?? `Erro HTTP ${response.status}.`);
    }
    return data;
};
const api = {
    async getJson(url) {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' }
        });
        return parseResponse(response);
    },
    async postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify(payload)
        });
        return parseResponse(response);
    }
};
window.drozApi = api;
//# sourceMappingURL=api.js.map