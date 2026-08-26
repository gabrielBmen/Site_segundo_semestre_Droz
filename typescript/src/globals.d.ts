declare global {
    interface Window {
        drozApi: ApiClient;
        bootstrap?: {
            Modal: new (element: Element) => {
                show(): void;
                hide(): void;
            };
        };
        produtoSearchTimer?: number;
    }
}

export {};
