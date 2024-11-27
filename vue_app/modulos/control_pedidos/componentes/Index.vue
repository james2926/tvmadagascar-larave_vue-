<template>
    <div>
        <loader v-if="isloading"></loader>
        <v-card shaped class="mx-4 my-4 pa-4">
            <v-card-title>
                <v-toolbar flat color="#1d2735" dark style="border-radius: 5px">
                    <v-icon class="white--text" style="font-size: 45px"
                        >mdi-account-supervisor-circle</v-icon
                    >
                    <pre><v-toolbar-title><h2>Control de pedidos</h2></v-toolbar-title></pre>
                </v-toolbar>
            </v-card-title>
            <v-card-text>
                <v-form class="mt-3">
                    <v-row justify="space-between" class="mb-3">
                        <v-col cols="12" sm="4" md="4">
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn
                                        icon
                                        color="primary"
                                        large
                                        v-bind="attrs"
                                        v-on="on"
                                        @click="getControl('anteror')"
                                    >
                                        <v-icon
                                            >mdi-arrow-left-bold-circle-outline</v-icon
                                        >
                                    </v-btn>
                                </template>
                                <span>Dia anterior</span>
                            </v-tooltip>
                        </v-col>
                        <v-col cols="12" sm="4" md="4">
                            <div class="d-flex justify-center">
                                Fecha: {{ fecha_formateada }}
                            </div>
                        </v-col>
                        <v-col
                            cols="12"
                            sm="4"
                            md="4"
                            class="d-flex justify-end"
                        >
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn
                                        icon
                                        color="primary"
                                        large
                                        v-bind="attrs"
                                        v-on="on"
                                        @click="getControl('siguiente')"
                                    >
                                        <v-icon
                                            >mdi-arrow-right-bold-circle-outline</v-icon
                                        >
                                    </v-btn>
                                </template>
                                <span>Dia siguiente</span>
                            </v-tooltip>
                        </v-col>
                    </v-row>

                    <!-- Orden de fabricacion -->
                    <div style="padding-bottom: 2rem">
                        <h1>Orden de Fabricacion</h1>
                    </div>

                    <div
                        class="box-shadow"
                        style="
                            overflow-x: auto;
                            border-radius: 4px;
                            padding-bottom: 2rem;
                        "
                    >
                        <table
                            style="
                                min-width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                            "
                        >
                            <tr class="t-header">
                                <th class="t-header-items">Articulo</th>
                                <th
                                    class="t-header-items align-center"
                                    v-for="item in orden.articulos"
                                >
                                    {{ item.articulo.nombre }}
                                </th>
                            </tr>
                            <tr v-if="orden.urgencia == 0">
                                <th class="t-header-items align-left">
                                    Inventario Inicial
                                </th>
                                <td v-for="item in orden.articulos">
                                    <!-- {{ item.inventario_inicial }} -->
                                    <v-text-field
                                        class="mt-2"
                                        outlined
                                        v-model="item.inventario_inicial"
                                        dense
                                    ></v-text-field>
                                </td>
                            </tr>
                            <tr>
                                <th class="t-header-items align-left">
                                    Inventario final
                                </th>
                                <td v-for="item in orden.articulos">
                                    <!-- <span>{{
                                        item.total_fabricar
                                    }}</span> -->
                                    <v-text-field
                                        outlined
                                        dense
                                        v-model="item.total_fabricar"
                                    ></v-text-field>
                                </td>
                            </tr>
                            <tr>
                                <th class="t-header-items align-left">
                                    Inventario total (suma inventarios)
                                </th>
                                <td v-for="item in orden.articulos">
                                    <span>{{
                                        parseFloat(item.inventario_inicial) +
                                        parseFloat(item.total_fabricar)
                                    }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="t-header-items align-left">
                                    Ventas diarias
                                </th>
                                <td v-for="item in orden.articulos">
                                    <template v-for="item2 in ventas_diarias">
                                        <span
                                            v-if="
                                                item.articulo.nombre ==
                                                item2.bola
                                            "
                                        >
                                            {{ item2.venta }}
                                        </span>
                                    </template>
                                </td>
                            </tr>
                            <!-- <tr>
                                <th class="t-header-items align-left">
                                    Total de Torres a Fabricar
                                </th>
                                <td v-for="item in orden.articulos">
                                    {{ TorreFormat(item) }}
                                </td>
                            </tr> -->
                            <tr>
                                <th class="t-header-items align-left">Resto</th>
                                <td v-for="item in orden.articulos">
                                    <template v-for="item2 in ventas_diarias">
                                        <span
                                            v-if="
                                                item.articulo.nombre ==
                                                item2.bola
                                            "
                                        >
                                            <!-- {{ (item2.inventario_inicial + item2.total_fabricar) - item.venta }} -->
                                            {{
                                                calcularStock(
                                                    item.inventario_inicial,
                                                    item.total_fabricar,
                                                    item2.venta
                                                )
                                            }}
                                        </span>
                                    </template>
                                </td>
                            </tr>
                        </table>
                        <!-- <v-col cols="12">
                            <v-textarea
                                dense
                                outlined
                                label="Observaciones"
                                v-model="orden.observaciones"
                            ></v-textarea>
                        </v-col> -->
                    </div>

                    <div class="d-flex justify-end mt-2">
                        <v-btn
                            @click="changeInventarios()"
                            :disabled="isloading"
                            color="success"
                            class="white--text"
                            >Actualizar Inventarios</v-btn
                        >
                    </div>

                    <!-- Empaquetado -->
                    <div style="padding: 2rem 0">
                        <h1>Empaquetado</h1>
                    </div>
                    <v-data-table
                        :item-class="row_classes"
                        dense
                        :items="pedidos"
                        :headers="headers"
                        :items-per-page="-1"
                        item-key="id"
                        class="elevation-1"
                        :sort-by="['nombre']"
                        :sort-desc="[false]"
                        :expand-on-click="true"
                    >
                        <template v-slot:header>
                            <thead>
                                <tr>
                                    <th colspan="3" class="text-right">
                                        Total por tipo de pizza
                                    </th>
                                    <th></th>
                                    <th
                                        v-for="(item, index) in prependHeader"
                                        :key="index"
                                    >
                                        {{ item.value }}
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">
                                        Total unidades por dia
                                    </th>
                                    <th>{{ total_und_dia }}</th>
                                    <th :colspan="prependHeader.length"></th>
                                </tr>
                            </thead>
                        </template>
                    </v-data-table>
                </v-form>
            </v-card-text>
        </v-card>
    </div>
</template>
<style>
.t-header {
    min-width: 100%;
    background-color: rgb(29, 39, 53);
    text-align: left;
    color: white;
    padding: 1rem;
}
.align-center {
    text-align: center;
    vertical-align: center;
}
.align-left {
    text-align: left;
    vertical-align: center;
    display: flex;
    align-items: center;
}
.t-header-items {
    width: 15rem;
    padding: 0.5rem;
}
.box-shadow {
    box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2),
        0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12) !important;
}
</style>
<script>
import moment from "moment";
export default {
    data() {
        return {
            orden: {
                fecha: null,
                articulos: [],
                pedidos: [],
                urgencia: 0,
            },
            pedidos: [],
            constante: {
                incremento_inv: null,
                total_gr_masa: null,
                capacidad_max: null,
            },
            headers: [
                { text: "Ref", value: "id", sortable: false },
                {
                    text: "Cliente",
                    value: "cliente.nombre_fiscal",
                    sortable: false,
                },
                {
                    text: "Código envió",
                    value: "cliente.codigo_envio",
                    sortable: false,
                },
                {
                    text: "Total Pedido",
                    value: "total_cajas",
                    sortable: false,
                },
            ],
            prependHeader: [],
            total_und_dia: 0,
            ventas_diarias: [],
            fecha: moment().format("YYYY-MM-DD").substring(0, 10),
        };
    },
    created() {
        this.getConstantes();
        this.getHeaders();
        this.generarOrden();
        this.getControl(null);
    },
    methods: {
        getConstantes() {
            axios.get("api/get-constantes").then((res) => {
                this.constante = res.data;
            });
        },
        getHeaders() {
            // axios.get(`api/get-headers-produccion`).then(
            axios.get(`api/get-headers-control-pedidos`).then(
                (res) => {
                    this.headers = res.data;
                },
                (err) => {
                    this.$custom_error("Error consultando Articulo");
                }
            );
        },
        getControl(string) {
            if (string != null) {
                if (string == "siguiente") {
                    this.fecha = moment(this.fecha)
                        .add(1, "days")
                        .format("YYYY-MM-DD")
                        .substring(0, 10);
                } else {
                    this.fecha = moment(this.fecha)
                        .subtract(1, "days")
                        .format("YYYY-MM-DD")
                        .substring(0, 10);
                }
            }
            axios.get(`api/get-control-pedidos?fecha=${this.fecha}`).then(
                (res) => {
                    this.orden = res.data.success.fabricacion ?? {};
                    this.pedidos = res.data.success.pedidos;
                    this.ventas_diarias = res.data.success.ventas_diarias;
                    this.setPrependHeader();
                    this.calculateTotalUndDia();
                    // this.calcularTotalTorres()
                },
                (err) => {
                    this.$custom_error("Error consultando Articulo");
                }
            );
        },
        generarOrden() {
            axios.post(`api/generate-produccion`).then(
                (res) => {
                    // this.orden = res.data;
                },
                (err) => {
                    this.$custom_error("Error consultando Articulo");
                }
            );
        },
        changeInventarios() {
            axios.post(`api/change-inventarios`, this.orden).then(
                (res) => {
                    this.$toast.sucs(res.data.success);
                },
                (err) => {
                    this.$custom_error("Error consultando Articulo");
                }
            );
        },
        calcularTotalTorres() {
            let total_torres = 0;
            if (this.orden.articulos.length > 0) {
                this.orden.articulos.forEach((element2) => {
                    let torres = this.Torre(element2);
                    const separados = torres.toFixed(0);

                    const cajas =
                        element2.total_fabricar /
                            element2.articulo.unidades_caja -
                        parseInt(separados) * element2.articulo.unidades_torre;

                    const cajas_redondeadas = cajas.toFixed(2);
                    total_torres +=
                        parseFloat(separados) + parseFloat(cajas_redondeadas);
                });
            }

            this.total_torres = total_torres;
        },
        TorreFormat(item) {
            let torres = this.Torre(item);
            const separados = torres.toFixed(0);
            const cajas =
                item.total_fabricar / item.articulo.unidades_caja -
                parseInt(separados) * item.articulo.unidades_torre;
            // console.log(cajas);
            return `${separados} ${
                cajas > 0 ? `y ${cajas.toFixed(2)} cajas` : ""
            }`;
        },
        Torre(item) {
            return this.Caja(item) / item.articulo.unidades_torre;
        },
        Caja(item) {
            return item.total_fabricar / item.articulo.unidades_caja;
        },
        row_classes(item) {
            // if (item.pedido.id_estado == 2) {
            if (item.id_estado == 2) {
                return "bg-green";
            }
            // if (item.pedido.id_estado == 3 || item.pedido.id_estado == 4) {
            if (item.id_estado == 3 || item.id_estado == 4) {
                return "bg-yellow";
            }
            return "";
        },
        setPrependHeader() {
            this.prependHeader = [];
            let nuevoHeader = this.headers.slice(4);
            nuevoHeader.forEach((header) => {
                const key = header.value;

                // Calcula la suma de la columna 'key'
                let sumaCantidad = 0;
                this.pedidos.forEach((pedido) => {
                    if (pedido[key] != undefined) {
                        let valor = pedido[key];
                        // Si el valor es una fracción en formato "x/y"
                        if (typeof valor === "string" && valor.includes("/")) {
                            let [numerador, denominador] = valor
                                .split("/")
                                .map(Number);
                            // Divide el número en dos y resta los resultados
                            valor = denominador - numerador;
                        }
                        sumaCantidad += valor;
                    }
                });
                this.prependHeader.push({ text: key, value: sumaCantidad });
            });
        },
        calculateTotalUndDia() {
            this.ventas_diarias.forEach((element) => {
                this.total_und_dia += element.venta;
            });
        },
        calcularStock(inventarioInicial, totalFabricar, venta) {
            // Asegúrate de que los valores son números
            const inicial = Number(inventarioInicial) || 0;
            const fabricar = Number(totalFabricar) || 0;
            const ventaNum = Number(venta) || 0;

            // Realiza el cálculo
            return inicial + fabricar - ventaNum;
        },
    },
    watch: {},
    computed: {
        isloading() {
            return this.$store.getters.getloading;
        },
        fecha_formateada() {
            console.log(this.fecha);
            return moment(this.fecha).format("DD-MM-YYYY");
        },
    },
};
</script>
