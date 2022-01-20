<template>
  <div>
    <b-row>
      <b-col cols="12">
        <b-card border-variant="primary" header-bg-variant="primary" :header="'<strong>Cargar Stock Productos</strong> (' + totalFilas + ')'" tag="article" class="m-3 mt-3">
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

          <b-modal v-if="objeto" centered v-model="showModal" :title="tipoOperacion">
            <b-container>
              <b-row class="mb-3">
                <b-col
                  class="text-left">
                  <span class="font-weight-bold">
                    <span style="color:red;">* </span>Producto
                  </span>:<br>
                  <span>
                    {{objeto.descripcion}}
                  </span>
                </b-col>
              </b-row>
              <b-row class="mb-3">
                <b-col
                  class="text-left">
                  <span class="font-weight-bold">
                    <span style="color:red;">* </span>Costo
                  </span>:<br>
                  <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
                    {{objeto.costo}}
                  </span>
                  <money v-else v-model="objeto.costo" class="form-control"></money>
                </b-col>
              </b-row>
              <b-row class="mb-3">
                <b-col
                  class="text-left">
                  <span class="font-weight-bold">
                    <span style="color:red;">* </span>Precio
                  </span>:<br>
                  <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
                    {{objeto.precio}}
                  </span>
                  <money v-else v-model="objeto.precio" class="form-control"></money>
                </b-col>
              </b-row>
              <b-row class="mb-3">
                <b-col
                  class="text-left">
                  <span class="font-weight-bold">
                    <span style="color:red;">* </span>Cantidad
                  </span>:<br>
                  <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
                    {{objeto.cantidad}}
                  </span>
                  <b-form-input
                    v-else
                    type="number"
                    required
                    v-model="objeto.cantidad"
                    class="form-control"/>
                </b-col>
              </b-row>
            </b-container>
            <div slot="modal-footer" v-if="objeto !== null" class="pull-right">
              <b-btn
                v-if="tipoOperacion === 'Modificar'"
                size="sm"
                variant="primary"
                @click.stop="guardarCambios">Guardar Cambios</b-btn>
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
import {Money} from 'v-money'
var self = this;
export default {
  name: "CargarStock",
  data: function() {
    return {
      campos: [
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
      estados: [],
      tipoProductos: []
    };
  },
  methods: {
    cargarFormulario: function(obj, operacion) {
      this.tipoOperacion = operacion;
      this.objeto = obj;
      if (obj === null) {
        this.objeto = {
          id: null,
          descripcion: null,
          orden: null,
          costo: '',
          precio: '',
          cantidad: null,
          estado: null,
          idtipoproducto: null,
          descripciontipoproducto: null
        };
      } else {
        this.selectedVisble = obj.visible
        this.objeto = {
          id: obj.id,
          descripcion: obj.descripcion,
          orden: obj.orden,
          costo: obj.costo,
          precio: obj.precio,
          cantidad: obj.cantidad,
          estado: obj.estado,
          idtipoproducto: obj.idtipoproducto,
          descripciontipoproducto: obj.descripciontipoproducto
        };
      }
      this.showModal = true;
    },
    validarCampos: function() {
      if (this.objeto.costo <= 0) {
        this.$toast.error("Debe escribir el costo");
        return false;
      }
      if (this.objeto.precio <= 0) {
        this.$toast.error("Debe escribir el precio");
        return false;
      }
      if (this.objeto.cantidad <= 0) {
        this.$toast.error("Debe escribir el cantidad");
        return false;
      }
      return true;
    },
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
    guardarCambios: function() {
      if(!this.validarCampos()) {
        return false
      }
      var self = this;
      self.$set(self.objeto, "token", window.localStorage.getItem("token"));
      self.objeto.visible = self.selectedVisble;
      this.$alertify
        .confirmWithTitle(
          "Modificar",
          "Seguro que desea modificar el registro?",
          function() {
            self.showModal = false;
            self.$loader.open({ message: "Guardando Cambios ..." });
            self.$http.put("ws/producto/", self.objeto).then(resp => {
                self.$loader.close();
                self.listar();
                self.$toast.success(resp.data.mensaje);
              })
              .catch(resp => {
                self.$loader.close();
                self.showModal = true;
                if (resp.data && resp.data.mensaje) {
                  self.$toast.error(resp.data.mensaje);
                } else {
                  self.$toast.error("error modificando");
                }
              });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    cerrarFormulario: function() {
      this.showModal = false;
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