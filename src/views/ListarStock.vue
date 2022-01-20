<template>
  <div>
    <b-row>
      <b-col cols="12">
        <b-card border-variant="primary" header-bg-variant="primary" :header="'<strong>Listar Stock Productos</strong> (' + totalFilas + ')'" tag="article" class="m-3 mt-3">
          <b-row class="mt-1 mb-2">
            <b-col cols="12">
              <b-form-group>
                <b-input-group cols="9">
                  <b-form-input
                    type="text"
                    v-model="filtro"
                    placeholder="Filtrar Búsqueda"
                    autocomplete="text"
                  ></b-form-input>
                  <!-- Attach Right button -->
                  <b-input-group-append>
                    <b-button variant="primary" :disabled="!filtro" @click.stop="filtro = ''">x</b-button>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row align-h="center">
            <b-col class="contenedor-tabla">
              <b-table
                v-if="items && items.length > 0"
                stacked="md"
                striped
                hover
                class="tabla"
                :fields="campos"
                :items="items"
                :current-page="paginaActual"
                :per-page="porPagina"
                :filter="filtro"
                @filtered="actualizarTabla">
                <template slot="acciones" slot-scope="row">
                  <b-button
                    style="margin: 1px;"
                    size="sm"
                    variant="primary"
                    @click.stop="cargarFormulario(row.item,'Modificar')">
                    <i class="icon-pencil"></i>
                  </b-button>
                </template>
              </b-table>
              <b-alert v-else show>No se encontraron registros.</b-alert>
            </b-col>
          </b-row>

          <b-row class="mb-5">
            <b-col>
              <b-pagination
                align="center"
                :total-rows="totalFilas"
                :per-page="porPagina"
                v-model="paginaActual"
                class="my-0"
              />
            </b-col>
          </b-row>

        </b-card>
      </b-col>
    </b-row>
  </div>
</template>
<style scoped>
.columna-centrada {
  text-align: center;
}
</style>

<script>
import {Money} from 'v-money'
var self = this;
export default {
  name: "CargarStock",
  data: function() {
    return {
      campos: [
        {
          key: "descripciontipoproducto",
          label: "Tipo Producto",
          sortable: true,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        },
        {
          key: "descripcion",
          label: "Descripción",
          sortable: true,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        },
        {
          key: "cantidad",
          label: "Cantidad",
          sortable: true,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        }
      ],
      items: [],
      objeto: null,
      showModal: false,
      tipoOperacion: "",
      totalFilas: 0,
      filtro: "",
      porPagina: 20,
      paginaActual: 1,
      estados: [],
      tipoProductos: []
    };
  },
  methods: {
    listar: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {params: {stock: true}};
      this.$http.get("ws/producto/", frm).then(resp => {
          self.items = resp.data;
          if (self.items && self.items.length > 0) {
            self.totalFilas = self.items.length;
          }
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items");
          }
        });
    },
    actualizarTabla: function(itemsFiltrados) {
      this.totalFilas = itemsFiltrados.length;
      this.paginaActual = 1;
    }
  },
  created: function() {
    this.listar();
  },
  mounted: function() {
    this.$loader.close();
  }
};
</script>