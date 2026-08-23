type ApiResult<T> = {
    sucesso: boolean;
    mensagem?: string;
    dados?: T;
    [key: string]: unknown;
};

type ApiClient = {
    getJson<T>(url: string): Promise<ApiResult<T>>;
    postJson<T>(url: string, payload: unknown): Promise<ApiResult<T>>;
};

const parseResponse = async <T>(response: Response): Promise<ApiResult<T>> => {
    const contentType = response.headers.get('content-type') ?? '';
    if (!contentType.includes('application/json')) {
        throw new Error(`Resposta inválida do servidor (HTTP ${response.status}).`);
    }

    const data = await response.json() as ApiResult<T>;
    if (!response.ok) {
        throw new Error(data.mensagem ?? `Erro HTTP ${response.status}.`);
    }

    return data;
};

const api: ApiClient = {
    async getJson<T>(url: string): Promise<ApiResult<T>> {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' }
        });
        return parseResponse<T>(response);
    },

    async postJson<T>(url: string, payload: unknown): Promise<ApiResult<T>> {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify(payload)
        });
        return parseResponse<T>(response);
    }
};

window.drozApi = api;
