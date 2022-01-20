<template>
  <div>
    <b-row>
      <b-col cols="12">
        <b-card border-variant="primary" header-bg-variant="primary" :header="'<strong>Cierre de caja</strong>'" tag="article" class="m-3 mt-3">
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
                      @click.stop="consultarCierreCaja(); consultarGastos();"
                      variant="primary">
                      Consultar </b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row class="mt-5 mb-2">
            <b-col cols="12" class="text-center" v-if="items && items.length > 0">
              <b-form-checkbox
                id="checkbox-1"
                v-model="limpiarBase"
                name="checkbox-1"
                value="SI"
                unchecked-value="NO"
              >
                ¿ Desea imprimir ticket ?
              </b-form-checkbox>
              <b-btn
                class="ml-3"
                @click.stop="generarInformeCierreCaja();"
                variant="primary">
                Generar Informe </b-btn>
            </b-col>
          </b-row>   

          <b-row class="mt-1 mb-2">
            <b-col cols="12" ref="htmlCierreCaja">
              <template v-if="items && items.length > 0">
                <h4 style='text-align:center'>Cierre de caja del dia {{filtro}} </h4>

                <table style='width:100%;'  cellspacing='0' cellpadding='0'>
                  <tr style='border: solid 1px black;'>
                    <th style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>PRODUCTO</th>
                    <th style='width:20%; border: solid 1px black; padding: 10px;text-align:center;'>PRECIO</th>
                    <th style='width:20%; border: solid 1px black; padding: 10px;text-align:center;'>CANTIDAD</th>
                    <th style='width:20%; border: solid 1px black; padding: 10px;text-align:center;'>TOTAL</th>
                  </tr>

                  <tr v-for="(item, i) in items" :key="'tr_item_' + i" style='border: solid 1px black;'>
                    <td style='width:40%;border: solid 1px black;padding: 5px;text-align:center;'>{{item.prod_descripcion}}</td>
                    <td style='width:20%;border: solid 1px black;padding: 5px;text-align:center;'>{{format(item.prod_precio)}}</td>
                    <td style='width:20%;border: solid 1px black;padding: 5px;text-align:center;'>{{item.cantidad}}</td>
                    <td style='width:20%;border: solid 1px black;padding: 5px;text-align:center;'>{{format(item.total)}}</td>
                  </tr>
                  <tr style='border: solid 1px black;'>
                    <td style='width:40%; padding: 10px;'></td>
                    <td style='width:20%; padding: 10px;'></td>
                    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>SUB. TOTAL</td>
                    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>{{getTotalVentas()}}</td>
                  </tr>

                </table>
                    
                <br>

                <table style='width:100%;'  cellspacing='0' cellpadding='0'>
                  <tr style='border: solid 1px black;'>
                    <th style='width:70%; border: solid 1px black; padding: 10px;text-align:center;'>EGRESO</th>
                    <th style='width:30%; border: solid 1px black; padding: 10px;text-align:center;'>COSTO</th>
                  </tr>

                  <tr v-for="(gasto, g) in gastos" :key="'tr_gasto_' + g" style='border: solid 1px black;'>
                    <td style='border: solid 1px black;padding: 5px;text-align:center;'>{{gasto.descripcion}}</td>
                   <td style='border: solid 1px black;padding: 5px;text-align:center;'>{{format(gasto.valor)}}</td>
                  </tr>
                  <tr style='border: solid 1px black;'>
                    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>SUB. TOTAL</td>
                    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>{{getTotalGastos()}}</td>
                  </tr>
                </table>

                <br>

                <table style='width:100%;'  cellspacing='0' cellpadding='0'>
                  <tr style='border: solid 1px black;'>
                    <th style='width:33%; border: solid 1px black; padding: 10px;text-align:center;'>INGRESO</th>
                    <th style='width:33%; border: solid 1px black; padding: 10px;text-align:center;'>EGRESO</th>
                    <th style='width:33%; border: solid 1px black; padding: 10px;text-align:center;'>TOTAL EN CAJA</th>
                  </tr>
                  <tr style='border: solid 1px black;'>
                    <th style='width:33%; border: solid 1px black; padding: 5px;text-align:center;'> {{getTotalVentas()}} </th>
                    <th style='width:33%; border: solid 1px black; padding: 5px;text-align:center;'> {{getTotalGastos()}} </th>
                    <th style='width:33%; border: solid 1px black; padding: 5px;text-align:center;'> {{getTotalCierre()}} </th>
                  </tr>
                </table>
              </template>
              <b-alert v-else show variant="danger">No hay items para hacer cierre de caja. Seleccione otra fecha y genere el cierre de caja.</b-alert>
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
  name: "CierreCaja",
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
      limpiarBase: "SI"
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
    consultarGastos: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        params: {
          fecha: this.filtro,
          gastos: 'SI'
        }
      };
      this.$http.get("ws/cierrecaja/", frm).then(resp => {
          self.gastos = resp.data;
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los gastos para hacer cierre de caja.");
          }
        });
    },
    generarInformeCierreCaja: function() {
      var self = this;
      this.$alertify
        .confirmWithTitle(
          "Guardar",
          "Seguro que desea generar el informe?",
          function() {
            let html = self.$refs.htmlCierreCaja.innerHTML;

            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", process.env.VUE_APP_URL + "dompdf/dompdf/www/generar-pdf.php");

            form.setAttribute("target", "view");

            var hiddenFieldNombre = document.createElement("input"); 
            hiddenFieldNombre.setAttribute("type", "hidden");
            hiddenFieldNombre.setAttribute("name", "nombre");
            hiddenFieldNombre.setAttribute("value", "Cierre de caja " + self.filtro + ".pdf");
            form.appendChild(hiddenFieldNombre);

            var hiddenFieldHtml = document.createElement("input"); 
            hiddenFieldHtml.setAttribute("type", "hidden");
            hiddenFieldHtml.setAttribute("name", "html");
            hiddenFieldHtml.setAttribute("value", html);
            form.appendChild(hiddenFieldHtml);

            document.body.appendChild(form);

            window.open('', 'view');

            form.submit();
            if (self.limpiarBase == 'SI') {
              self.depurarCierreCaja();
              self.imprimirTicketCierreCaja();
            }
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    imprimirTicketCierreCaja: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        fecha: this.filtro,
        items: this.items,
        gastos: this.gastos,
        titulo: 'Cierre de caja del dia ' + this.filtro
      };
      this.$http.post("../mesero/ws/ticket/cierre-caja.php", frm).then(resp => {
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo imprimir ticket al hacer cierre de caja.");
          }
        });
    },
    depurarCierreCaja: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        params: {
          fecha: this.filtro
        }
      };
      this.$http.get("ws/cierrecaja/depurar.php", frm).then(resp => {
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo depurar al hacer cierre de caja.");
          }
        });
    },
    cerrarFormulario: function() {
      this.showModal = false;
    },
    actualizarTabla: function (itemsFiltrados) {
      this.totalFilas = itemsFiltrados.length;
      this.paginaActual = 1;
    },
    getTotalVentas: function () {
      let total = 0;
      this.items.forEach(item => {
        total = total + parseInt(item.total);
      });
      return this.format(total.toString());
    },
    getTotalGastos: function () {
      let total = 0;
      this.gastos.forEach(gasto => {
        total = total + parseInt(gasto.valor);
      });
      return this.format(total.toString());
    },
    getTotalCierre: function () {
      let totalVentas = 0;
      this.items.forEach(item => {
        totalVentas = totalVentas + parseInt(item.total);
      });

      let totalGastos = 0;
      this.gastos.forEach(gasto => {
        totalGastos = totalGastos + parseInt(gasto.valor);
      });
      let total = totalVentas - totalGastos;
      return this.format(total.toString());
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
