<template>
  <div>
    <b-row>
      <b-col cols="12">
        <b-card border-variant="primary" header-bg-variant="primary" :header="'<strong>Ver Reportes</strong> (' + totalFilas + ')'" tag="article" class="m-3 mt-3">
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
                @filtered="actualizarTabla"
              >
                <template slot="acciones" slot-scope="row">
                  <b-button
                    style="margin: 1px;"
                    size="sm"
                    @click.stop="generarReporte(row.item)"
                  >
                    Generar Reporte
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

          <b-modal v-if="objeto" centered v-model="showModal" :title="tipoOperacion">
            <b-container>
              <b-row class="mb-3">
                <b-col
                  class="text-left">
                  <span class="font-weight-bold">
                    <span style="color:red;">* </span>Descripción
                  </span>:<br>
                  <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
                    {{objeto.descripcion}}
                  </span>
                  <b-form-input
                    v-else
                    type="text"
                    required
                    v-model="objeto.descripcion"
                    class="form-control"/>
                </b-col>
              </b-row>
              <b-row class="mb-3">
                <b-col
                  class="text-left">
                  <span class="font-weight-bold">
                    <span style="color:red;">* </span>Orden
                  </span>:<br>
                  <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
                    {{objeto.orden}}
                  </span>
                  <b-form-input
                    v-else
                    type="number"
                    required
                    v-model="objeto.orden"
                    class="form-control"/>                
                </b-col>
              </b-row>
              <b-row class="mb-3">
                <b-col
                  class="text-left">
                  <span class="font-weight-bold">
                    <span style="color:red;">* </span>Estado
                  </span>:<br>
                  <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
                    {{objeto.estado}}
                  </span>
                  <b-form-select v-else v-model="objeto.estado" class="mb-3">
                    <option :key="'esta_' + e" v-for="(estado, e) in estados" :value="estado.descripcion">{{estado.descripcion}}</option>
                  </b-form-select>
                </b-col>
              </b-row>
            </b-container>
            <div slot="modal-footer" v-if="objeto !== null" class="pull-right">
              <b-btn
                v-if="tipoOperacion === 'Ver'"
                size="sm"
                variant="secondary"
                @click.stop="cerrarFormulario"
              >Cerrar</b-btn>
              <b-btn
                v-if="tipoOperacion === 'Agregar'"
                size="sm"
                variant="primary"
                @click.stop="guardar"
              >Guardar</b-btn>
              <b-btn
                v-if="tipoOperacion === 'Modificar'"
                size="sm"
                variant="primary"
                @click.stop="guardarCambios"
              >Guardar Cambios</b-btn>
              <b-btn
                v-if="tipoOperacion === 'Eliminar'"
                size="sm"
                variant="danger"
                @click.stop="eliminar"
              >Eliminar</b-btn>
            </div>
          </b-modal>
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
var self = this;
export default {
  name: "TipoProducto",
  data: function() {
    return {
      campos: [
        {
          key: "nombre",
          label: "Nombre",
          sortable: true,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        },
        {
          key: "descripcion",
          label: "Descripción",
          sortable: true,
          thStyle: "text-align:center;"
        },
        {
          key: "acciones",
          label: "Acciones",
          sortable: false,
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
      estados: []
    };
  },
  methods: {
    listar: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {};
      this.$http.get("ws/verreportes/", frm).then(resp => {
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
    generarReporte: function(item) {
      console.log(item)
      var self = this;
      self.$loader.open({ message: "Generando ..." });
      let frm = {
        id: item.id
      }
      var form = document.createElement("form");
      form.setAttribute("method", "get");
      form.setAttribute("action", process.env.VUE_APP_URL + "administracion-restaurante/ws/verreportes/generar.php");

      form.setAttribute("target", "view");

      var hiddenField2 = document.createElement("input"); 
      hiddenField2.setAttribute("type", "hidden");
      hiddenField2.setAttribute("name", "id");
      hiddenField2.setAttribute("value", item.id);
      form.appendChild(hiddenField2);

      document.body.appendChild(form);

      let v = window.open('', 'view');
      v.close();
      form.submit();
      self.$loader.close();
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
