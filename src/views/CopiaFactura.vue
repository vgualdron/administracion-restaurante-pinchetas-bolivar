<template>
  <div>
    <b-row>
      <b-col cols="12">
        <b-card border-variant="primary" header-bg-variant="primary" :header="'<strong>Generar copia de facturas</strong>'" tag="article" class="m-3 mt-3">
          <b-row class="mt-1 mb-2">
            <b-col cols="12">
              <b-form-group>
                <b-input-group cols="9">
                  <b-form-input
                    type="date"
                    v-model="filtro"
                    placeholder="Filtrar Búsqueda"
                    autocomplete="text"></b-form-input>
                  <!-- Attach Right button -->
                  <b-input-group-append>
                    <b-button variant="primary" :disabled="!filtro" @click.stop="filtro = ''">x</b-button>
                    <b-btn
                      class="ml-3"
                      @click.stop="consultarCierreCaja()"
                      variant="primary">
                      Consultar </b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row class="mt-1 mb-2">
            <b-col cols="12" ref="htmlCierreCaja">
              <template v-if="items && items.length > 0">
                <h4 style='text-align:center'>{{facturas.length}} Facturas del dia {{filtro}} </h4>

              </template>
              <b-alert v-else show variant="danger">No hay facturas. Seleccione otra fecha y consulte de nuevo.</b-alert>
            </b-col>
          </b-row>

          <b-row class="mt-5 mb-2">
            <b-col cols="12" class="text-center" v-if="items && items.length > 0">
              
              <b-btn
                class="ml-3"
                @click.stop="generarImpresion();"
                variant="primary">
                Imprimir Facturas </b-btn>
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
var self = this;
export default {
  name: "CopiaFactura",
  data: function() {
    return {
      date: null,
      items: [],
      gastos: [],
      objeto: null,
      showModal: false,
      tipoOperacion: "",
      totalFilas: 0,
      filtro: "",
      porPagina: 20,
      paginaActual: 1,
      imprimirTicket: "SI",
      dataReporte: null,
      facturas: []
    };
  },
  methods: {
    format: function (input){
      var num = input.replace(/\./g,'');
      if(!isNaN(num)){
        num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
        num = num.split('').reverse().join('').replace(/^[\.]/,'');
        return num;
      }
      return 'X';
    },
    consultarCierreCaja: function() {
      if (!this.filtro) {
        this.$toast.error("Debe de seleccionar la fecha para generar el cierre de caja");
        return false;
      }
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        params: {
          fecha: this.filtro,
          ventas: 'SI'
        }
      };
      this.$http.get("ws/cierrecaja/", frm).then(resp => {
          self.items = resp.data;
          self.$loader.close();
          if (self.items.length > 0) {
            self.consultarFacturas();
          }
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items para hacer cierre de caja.");
          }
        });
    },
    consultarFacturas: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        params: {
          fecha: this.filtro
        }
      };
      this.$http.get("ws/pedido/", frm).then(resp => {
          self.facturas = resp.data;
          self.$loader.close();
          self.recorrerFacturas();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener las facturas de la fecha seleccionada.");
          }
        });
    },
    recorrerFacturas: function() {
      var self = this;
      this.facturas.forEach(pedido => {
        self.consultarItemsPedido(pedido.id).then(resp => {
          pedido.productos = resp;
          console.log(resp);
        })
      });
      
    },
    consultarItemsPedido: function(idPedido) {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = { params: {
          idpedido: idPedido
        }
      };
      return this.$http.get("../mesero/ws/detallepedido/", frm).then(resp => {
          let items = resp.data;
          if (self.items && self.items.length > 0) {
            self.totalFilas = self.items.length;
          }
          self.$loader.close();
          return items;
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items de detalles del pedido.");
          }
          return [];
        });
    },
    generarImpresion: function() {
      var self = this;
      this.facturas.forEach(pedido => {
        self.imprimirTicketFactura(pedido);
      });
    },
    imprimirTicketFactura: function(pedido) {
      this.$loader.open({ message: "Cargando ..." });
      let token = window.localStorage.getItem("token");
      var self = this;
      var frm = {
        fecha: this.filtro,
        productos: pedido.productos,
        token: token,
        numerofactura: pedido.numerofactura,
        nombrecliente: pedido.nombrecliente,
        telefonocliente: pedido.telefonocliente,
        direccioncliente: pedido.direccioncliente,
        mesa: {
          descripcion: pedido.descripcionmesa
        }
      };
      this.$http.post("../mesero/ws/ticket/factura.php", frm).then(resp => {
          // self.items = resp.data;
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("Error al imprimir ticket de copia de factura.");
          }
        });
    },
    cerrarFormulario: function() {
      this.showModal = false;
    },
    actualizarTabla: function (itemsFiltrados) {
      this.totalFilas = itemsFiltrados.length;
      this.paginaActual = 1;
    }
  },
  created: function() {
    this.date = new Date();
    let dia = this.date.getDate();
    let mes = (this.date.getMonth() + 1);
    let ano = this.date.getFullYear();

    if (dia < 10) {
      dia = '0' + dia;
    }
     
    if (mes < 10) {
      mes = '0' + mes;
    }
    this.filtro = ano + '-' + mes + '-' + dia;
  },
  mounted: function() {
    this.$loader.close();
  }
};
</script>
