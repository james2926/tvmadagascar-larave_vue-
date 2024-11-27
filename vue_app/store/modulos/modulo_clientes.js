import Vue from "vue";

const modulo_clientes = {
    strict: false,

    state: {
        clientes: [],
        grupos: [],
        current: 0,
    },

    getters: {
        getclientes: (state) => state.clientes,
        getgruposclientes: (state) => state.grupos,
    },

    mutations: {
        get_clientes: (state, clientes) => {
            state.clientes = clientes.data;
            state.page = 2;
        },
        get_grupos_cliente: (state, grupos) => {
            state.grupos = grupos;
        },
        update_clientes: (state, clientes) => {
            state.clientes = state.clientes.concat(clientes.data);
            state.page++;
        },
        add_search: (state, clientes) => {
            state.clientes = state.clientes.concat(clientes.data);
        },
    },

    actions: {
        getGruposCliente: (context, vm) => {
            axios.get("api/get-grupos-cliente").then(
                (res) => {
                    context.commit("get_grupos_cliente", res.data);
                },
                (res) => {}
            );
        },
        getClientes: (context, vm) => {
            axios.get("api/get-clientes?cantidad=15&page=1").then(
                (res) => {
                    context.commit("get_clientes", res.data);
                },
                (res) => {}
            );
        },
        getClientesWithId: (context, vm) => {
            axios
                .get(`api/get-clientes?cantidad=15&page=1&cliente=${vm.id}`)
                .then(
                    (res) => {
                        context.commit("get_clientes", res.data);
                    },
                    (res) => {}
                );
        },
        getClientesNext: (context, vm) => {
            axios
                .get(`api/get-clientes?cantidad=15&page=${context.state.page}`)
                .then(
                    (res) => {
                        context.commit("update_clientes", res.data);
                    },
                    (res) => {}
                );
        },
        searchCliente: (context, vm) => {
            axios
                .get(
                    `api/get-clientes?cantidad=15&page=1${
                        vm ? `&busqueda=${vm}` : ""
                    }`
                )
                .then(
                    (res) => {
                        context.commit("update_clientes", res.data);
                    },
                    (res) => {}
                );
        },
        getDataCliente(context, vm) {
            axios.get(`api/get-cliente/${vm}`).then(
                (res) => {
                    context.commit("get_data_cliente", res.data);
                },
                (res) => {}
            );
        },
    },
};

export default modulo_clientes;
