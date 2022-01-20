<template>
  <div>
    <b-row>
      <b-col cols="12">
        <b-card border-variant="primary" header-bg-variant="primary" :header="'<strong>Control Fiscal</strong>'" tag="article" class="m-3 mt-3">
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

          <b-row class="mt-5 mb-2">
            <b-col cols="12" class="text-center" v-if="items && items.length > 0">
              <b-form-checkbox
                id="checkbox-1"
                v-model="imprimirTicket"
                name="checkbox-1"
                value="SI"
                unchecked-value="NO"
              >
                ¿ Desea imprimir ticket ?
              </b-form-checkbox>
              <b-btn
                class="ml-3"
                @click.stop="generarReporteControlFiscal();"
                variant="primary">
                Generar Informe </b-btn>
            </b-col>
          </b-row>   

          <b-row class="mt-1 mb-2">
            <b-col cols="12" ref="htmlCierreCaja">
              <template v-if="items && items.length > 0">
                <h4 style='text-align:center'>Control fiscal del dia {{filtro}} </h4>

                <table style='width:100%;'  cellspacing='0' cellpadding='0'>
                  <tr style='border: solid 1px black;'>
                    <th colspan="4" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>VENTAS POR FORMA DE PAGO</th>
                  </tr>
                </table>
                
                <br>

                <table v-if="dataReporte" style='width:100%;'  cellspacing='0' cellpadding='0'>
                  
                  <tr style='border: solid 1px black;'>
                    <td colspan="2" style='border: solid 1px black; padding: 10px;text-align:center;'>TOTAL FACTURADO</td>
                    <td colspan="2" style='border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.totalRegistrado}}</td>
                  </tr>

                  <tr style='border: solid 1px black;'>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>{{dataReporte.cantidadVentasEfectivo}}</td>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>EFECTIVO</td>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>{{dataReporte.porcentajeVentasEfectivo}}</td>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.totalVentasEfectivo}}</td>
                  </tr>

                  <tr style='border: solid 1px black;'>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>{{dataReporte.cantidadVentasTarjeta}}</td>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>TARJETA CRED / DEBI</td>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>{{dataReporte.porcentajeVentasTarjeta}}</td>
                    <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.totalVentasTarjeta}}</td>
                  </tr>
                  <tr style='border: solid 1px black;'>
                    <td colspan="2" style='border: solid 1px black; padding: 10px;text-align:center;'>TOTAL FORMA DE PAGO</th>
                    <td colspan="2" style='border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.totalRegistrado}}</td>
                  </tr>

                </table>
                    
                <br>

                <table style='width:100%;'  cellspacing='0' cellpadding='0'>
                  <tr style='border: solid 1px black;'>
                    <th colspan="4" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>VENTAS POR DEPARTAMENTO</th>
                  </tr>
                </table>
               
                <template v-for="(departamento, d) in dataReporte.departamentos">
                  <br :key="'br_depa_' + d">
                  <table :key="'table_depa_' + d" style='width:100%;'  cellspacing='0' cellpadding='0'>
                    
                    <tr style='border: solid 1px black;'>
                      <th colspan="4" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>DEPARTAMENTO {{departamento.nombre}}</th>
                    </tr>

                    <tr style='border: solid 1px black;'>
                      <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>GRAV</td>
                      <td colspan="2" style='border: solid 1px black; padding: 10px;text-align:center;'>VR BASE</td>
                      <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>VR ICO</td>
                    </tr>

                    <tr style='border: solid 1px black;'>
                      <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>{{(departamento.grav) * 100}}</td>
                      <td colspan="2" style='border: solid 1px black; padding: 10px;text-align:center;'>${{departamento.valorBase}}</td>
                      <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>${{departamento.valorIco}}</td>
                    </tr>

                    <tr style='border: solid 1px black;'>
                      <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>TOTAL</td>
                      <td colspan="2" style='border: solid 1px black; padding: 10px;text-align:center;'>${{departamento.valorBase}}</td>
                      <td colspan="1" style='border: solid 1px black; padding: 10px;text-align:center;'>${{departamento.valorIco}}</td>
                    </tr>

                  </table>
                </template> 
                <br>

                <table style='width:100%;'  cellspacing='0' cellpadding='0'>
                  <tr style='border: solid 1px black;'>
                    <th colspan="2" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>TOTAL DEPARTAMENTOS</th>
                  </tr>
                  <tr style='border: solid 1px black;'>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>VR BASE</td>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>VR ICO</td>
                  </tr>
                  <tr style='border: solid 1px black;'>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.totalValorBaseDepartamentos}}</td>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.totalValorIcoDepartamentos}}</td>
                  </tr>
                </table>

                <br>

                <table style='width:100%;'  cellspacing='0' cellpadding='0'>
                  <tr style='border: solid 1px black;'>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>VENTAS NETAS</td>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.ventasNetas}}</td>
                  </tr>
                  <tr style='border: solid 1px black;'>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>TOTAL REGISTRADO</td>
                    <td colspan="1" style='width:40%; border: solid 1px black; padding: 10px;text-align:center;'>${{dataReporte.totalRegistrado}}</td>
                  </tr>
                </table>

              </template>
              <b-alert v-else show variant="danger">No hay items para hacer el reporte de control fiscal. Seleccione otra fecha y consulte de nuevo.</b-alert>
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
  name: "ControlFiscal",
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
      dataReporte: null
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
            self.consultarReporteControlFiscal();
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
    consultarReporteControlFiscal: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        fecha: this.filtro
      };
      this.$http.post("ws/controlfiscal/", frm).then(resp => {
          self.dataReporte = resp.data;
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los datos para hacer el reporte de control fiscal.");
          }
        });
    },
    generarReporteControlFiscal: function() {
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
            hiddenFieldNombre.setAttribute("value", "Control fiscal " + self.filtro + ".pdf");
            form.appendChild(hiddenFieldNombre);

            var hiddenFieldHtml = document.createElement("input"); 
            hiddenFieldHtml.setAttribute("type", "hidden");
            hiddenFieldHtml.setAttribute("name", "html");
            hiddenFieldHtml.setAttribute("value", html);
            form.appendChild(hiddenFieldHtml);

            document.body.appendChild(form);

            window.open('', 'view');

            form.submit();
            if (self.imprimirTicket == 'SI') {
              self.imprimirTicketControlFiscal();
            }
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    imprimirTicketControlFiscal: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = {
        fecha: this.filtro,
        data: this.dataReporte
      };
      this.$http.post("../mesero/ws/ticket/control-fiscal.php", frm).then(resp => {
          // self.items = resp.data;
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("Error al imprimir ticket de control fiscal.");
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
