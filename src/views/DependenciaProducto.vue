<template>
  <div>
    <b-row>
      <b-col cols="12">
        <b-card border-variant="primary" header-bg-variant="primary" :header="'<strong>Gestionar Dependencia de Productos</strong> (' + totalFilas + ')'" tag="article" class="m-3 mt-3">
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
                    <b-btn
                      class="ml-3"
                      @click.stop="cargarFormulario(null,'Agregar')"
                      variant="primary"
                    >Nuevo</b-btn>
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
                    <span style="color:red;">* </span>Productos de los que depende
                  </span>:<br>
                  <b-form-group v-if="objeto.id">
                    <b-form-checkbox-group
                      stacked
                      v-model="productosSeleccionados"
                      id="checkboxes1"
                      name="check-productos">
                      <b-form-checkbox :key="'prod_' + p" v-for="(producto, p) in items" :value="{idhijo: producto.id}">{{producto.descripcion}}</b-form-checkbox>
                    </b-form-checkbox-group>  
                  </b-form-group>
                  <b-alert v-else show variant="info">Debe Seleccionar el producto.</b-alert>
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
                v-if="tipoOperacion === 'Modificar'"
                size="sm"
                variant="primary"
                @click.stop="guardarCambios"
              >Guardar Cambios</b-btn>
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
  name: "DependenciaProducto",
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
          key: "descripciontipoproducto",
          label: "Tipo Producto",
          sortable: true,
          thStyle: "text-align:center;"
        },
        {
          key: "estado",
          label: "Estado",
          sortable: true,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        },
        {
          key: "precio",
          label: "Precio",
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
      tipoProductos: [],
      productosSeleccionados: []
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
        this.listarDependenciaProducto(obj.id)
      }
      this.showModal = true;
    },
    validarCampos: function() {
      /* if (this.productosSeleccionados.length < 1) {
        this.$toast.error("Debe de seleccionar al menos un producto, si no depende de ningun producto, seleccione el mismo porducto padre como producto hijo.");
        return false;
      } */
      return true;
    },
    listarEstados: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {};
      this.$http.get("ws/estado/", frm).then(resp => {
          self.estados = resp.data;
          self.$loader.close();
        }).catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items de estados");
          }
        });
    },
    listarTipoProductos: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {};
      this.$http.get("ws/tipoproducto/", frm).then(resp => {
          self.tipoProductos = resp.data;
          self.$loader.close();
        }).catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items de tipos de productos");
          }
        });
    },
    listarDependenciaProducto: function(idpadre) {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        params: {idpadre: idpadre}
      };
      this.$http.get("ws/dependenciaproducto/", frm).then(resp => {
          self.productosSeleccionados = resp.data;
          self.$loader.close();
        }).catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items dependencias de productos");
          }
        });
    },
    listar: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {};
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
      self.$set(self.objeto, "hijos", self.productosSeleccionados);
      this.$alertify
        .confirmWithTitle(
          "Modificar",
          "Seguro que desea modificar el registro?",
          function() {
            self.showModal = false;
            self.$loader.open({ message: "Guardando Cambios ..." });
            self.$http.post("ws/dependenciaproducto/", self.objeto).then(resp => {
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
    this.listarEstados();
    this.listarTipoProductos();
  },
  mounted: function() {
    this.$loader.close();
  }
};
</script>