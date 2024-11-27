<template>
    <v-card class="pa-3 ma-3">
        <v-toolbar flat color="#1d2735" dark>
            <v-icon class="white--text" style="font-size: 45px"
                >mdi-account-supervisor-circle</v-icon
            >
            <pre><v-toolbar-title><h2>Lista Articulos</h2></v-toolbar-title></pre>
        </v-toolbar>
        <v-tooltip right>
            <template v-slot:activator="{ on, attrs }">
                <v-btn
                    fab
                    :to="{ path: `/guardar-articulo` }"
                    :loading="isloading"
                    :disabled="isloading"
                    color="orange darken-1"
                    class="mt-2"
                    v-bind="attrs"
                    v-on="on"
                >
                    <v-icon class="white--text"
                        >mdi-account-plus-outline</v-icon
                    >
                </v-btn>
            </template>
            <span>Nuevo Articulo</span>
        </v-tooltip>
        <v-row>
            <v-col cols="12" md="4">
                <v-text-field-outlined
                    prepend-icon="mdi-account-search"
                    v-model="search"
                    label="Buscar"
                ></v-text-field-outlined>
            </v-col>
        </v-row>
        <v-data-table
            :item-class="
                () => {
                    return 'pointer';
                }
            "
            @click:row="
                (item) => {
                    $router.push(`/guardar-articulo?id=${item.id}`);
                }
            "
            dense
            :headers="headers"
            :items="Articulos"
            :search="search"
            :items-per-page="15"
            item-key="id"
            class="elevation-1"
            :sort-by="['nombre']"
            :sort-desc="[false]"
        >
            <template v-slot:item.fecha_alta="{ item }">
                <span>{{ item.fecha_alta }}</span>
            </template>
            <template v-slot:item.action="{ item }">
                <v-icon
                    @click.stop="openModal(item)"
                    small
                    class="mr-2"
                    color="red"
                    style="font-size: 25px"
                    title="BORRAR"
                    >mdi-trash-can</v-icon
                >
            </template>
        </v-data-table>
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title
                    class="text-h5 aviso"
                    style="
                        justify-content: center;
                        background: #1d2735;
                        color: white;
                    "
                >
                    Aviso
                </v-card-title>
                <v-card-text style="text-align: center">
                    <h2>¿Estás seguro que deseas eliminar?</h2>
                </v-card-text>
                <v-card-actions class="pt-3">
                    <v-spacer></v-spacer>
                    <v-btn
                        color="error"
                        large
                        @click="
                            dialog = false;
                            selectedItem = {};
                        "
                        >Cancelar</v-btn
                    >
                    <v-btn color="success" large @click="deleteUser()"
                        >Confirmar</v-btn
                    >
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>
<script>
export default {
    data() {
        return {
            search: "",
            headers: [
                { text: "Ref", value: "ref", sortable: false },
                { text: "Nombre", value: "nombre", sortable: false },
                { text: "Descripcion", value: "descripcion", sortable: false },
                {
                    text: "Principal",
                    value: "produccion.nombre",
                    sortable: true,
                },

                { text: "Cantidad", value: "cantidad", sortable: false },
                { text: "Peso", value: "peso", sortable: false },
                { text: "Acciones", value: "action", sortable: false },
            ],
            Articulos: [],
            selectedItem: 0,
            dialog: false,
        };
    },
    created() {
        this.getArticulos();
    },
    methods: {
        getArticulos() {
            axios.get(`api/get-articulos`).then(
                (res) => {
                    this.Articulos = res.data;
                },
                (err) => {
                    this.$custom_error("Error consultando Articulo");
                }
            );
        },
        openModal(item) {
            this.selectedItem = this.Articulos.indexOf(item);
            this.dialog = true;
        },
        deleteUser() {
            axios
                .get(
                    `api/delete-articulo/${
                        this.Articulos[this.selectedItem].id
                    }`
                )
                .then(
                    (res) => {
                        this.$toast.sucs("Articulo eliminado");
                        this.dialog = false;
                        this.getArticulos();
                    },
                    (err) => {
                        this.$custom_error("Error eliminando Articulo");
                    }
                );
        },
    },
    computed: {
        isloading: function () {
            // return this.$store.getters.getloading
        },
    },
};
</script>
