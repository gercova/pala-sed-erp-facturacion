<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // AMAZONAS - CHACHAPOYAS (0101)
        $districts = array(
            ['codigo' => '010101', 'descripcion' => 'Chachapoyas', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010102', 'descripcion' => 'Asunción', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010103', 'descripcion' => 'Balsas', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010104', 'descripcion' => 'Cheto', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010105', 'descripcion' => 'Chiliquín', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010106', 'descripcion' => 'Chuquibamba', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010107', 'descripcion' => 'Granada', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010108', 'descripcion' => 'Huancas', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010109', 'descripcion' => 'La Jalca', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010110', 'descripcion' => 'Leimebamba', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010111', 'descripcion' => 'Levanto', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010112', 'descripcion' => 'Magdalena', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010113', 'descripcion' => 'Mariscal Castilla', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010114', 'descripcion' => 'Molinopampa', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010115', 'descripcion' => 'Montevideo', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010116', 'descripcion' => 'Olleros', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010117', 'descripcion' => 'Quinjalca', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010118', 'descripcion' => 'San Francisco de Daguas', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010119', 'descripcion' => 'San Isidro de Maino', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010120', 'descripcion' => 'Soloco', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
            ['codigo' => '010121', 'descripcion' => 'Sonche', 'provincia_codigo' => '0101', 'departamento_codigo' => '01'],
        );

        // AMAZONAS - BAGUA (0102)
        $districts = array_merge($districts, [
            ['codigo' => '010201', 'descripcion' => 'Bagua', 'provincia_codigo' => '0102', 'departamento_codigo' => '01'],
            ['codigo' => '010202', 'descripcion' => 'Aramango', 'provincia_codigo' => '0102', 'departamento_codigo' => '01'],
            ['codigo' => '010203', 'descripcion' => 'Copallín', 'provincia_codigo' => '0102', 'departamento_codigo' => '01'],
            ['codigo' => '010204', 'descripcion' => 'El Parco', 'provincia_codigo' => '0102', 'departamento_codigo' => '01'],
            ['codigo' => '010205', 'descripcion' => 'Imaza', 'provincia_codigo' => '0102', 'departamento_codigo' => '01'],
            ['codigo' => '010206', 'descripcion' => 'La Peca', 'provincia_codigo' => '0102', 'departamento_codigo' => '01'],
        ]);

        // AMAZONAS - BONGARÁ (0103)
        $districts = array_merge($districts, [
            ['codigo' => '010301', 'descripcion' => 'Jumbilla', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010302', 'descripcion' => 'Chisquilla', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010303', 'descripcion' => 'Churuja', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010304', 'descripcion' => 'Corosha', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010305', 'descripcion' => 'Cuispes', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010306', 'descripcion' => 'Florida', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010307', 'descripcion' => 'Jazán', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010308', 'descripcion' => 'Recta', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010309', 'descripcion' => 'San Carlos', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010310', 'descripcion' => 'Shipasbamba', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010311', 'descripcion' => 'Valera', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
            ['codigo' => '010312', 'descripcion' => 'Yámbrasbamba', 'provincia_codigo' => '0103', 'departamento_codigo' => '01'],
        ]);

        // AMAZONAS - CONDORCANQUI (0104)
        $districts = array_merge($districts, [
            ['codigo' => '010401', 'descripcion' => 'Nieva', 'provincia_codigo' => '0104', 'departamento_codigo' => '01'],
            ['codigo' => '010402', 'descripcion' => 'El Cenepa', 'provincia_codigo' => '0104', 'departamento_codigo' => '01'],
            ['codigo' => '010403', 'descripcion' => 'Río Santiago', 'provincia_codigo' => '0104', 'departamento_codigo' => '01'],
        ]);

        // AMAZONAS - LUYA (0105)
        $districts = array_merge($districts, [
            ['codigo' => '010501', 'descripcion' => 'Lamud', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010502', 'descripcion' => 'Camporredondo', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010503', 'descripcion' => 'Cocabamba', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010504', 'descripcion' => 'Colcamar', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010505', 'descripcion' => 'Conila', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010506', 'descripcion' => 'Inguilpata', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010507', 'descripcion' => 'Longuita', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010508', 'descripcion' => 'Lonya Chico', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010509', 'descripcion' => 'Luya', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010510', 'descripcion' => 'Luya Viejo', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010511', 'descripcion' => 'María', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010512', 'descripcion' => 'Ocalli', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010513', 'descripcion' => 'Ocumal', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010514', 'descripcion' => 'Pisuquia', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010515', 'descripcion' => 'Providencia', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010516', 'descripcion' => 'San Cristóbal', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010517', 'descripcion' => 'San Francisco del Yeso', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010518', 'descripcion' => 'San Jerónimo', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010519', 'descripcion' => 'San Juan de Lopecancha', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010520', 'descripcion' => 'Santa Catalina', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010521', 'descripcion' => 'Santo Tomás', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010522', 'descripcion' => 'Tingo', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
            ['codigo' => '010523', 'descripcion' => 'Trita', 'provincia_codigo' => '0105', 'departamento_codigo' => '01'],
        ]);

        // AMAZONAS - RODRÍGUEZ DE MENDOZA (0106)
        $districts = array_merge($districts, [
            ['codigo' => '010601', 'descripcion' => 'San Nicolás', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010602', 'descripcion' => 'Chirimoto', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010603', 'descripcion' => 'Cochamal', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010604', 'descripcion' => 'Huambo', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010605', 'descripcion' => 'Limabamba', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010606', 'descripcion' => 'Longar', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010607', 'descripcion' => 'Mariscal Benavides', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010608', 'descripcion' => 'Milpuc', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010609', 'descripcion' => 'Omia', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010610', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010611', 'descripcion' => 'Totora', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
            ['codigo' => '010612', 'descripcion' => 'Vista Alegre', 'provincia_codigo' => '0106', 'departamento_codigo' => '01'],
        ]);

        // AMAZONAS - UTCUBAMBA (0107)
        $districts = array_merge($districts, [
            ['codigo' => '010701', 'descripcion' => 'Bagua Grande', 'provincia_codigo' => '0107', 'departamento_codigo' => '01'],
            ['codigo' => '010702', 'descripcion' => 'Cajaruro', 'provincia_codigo' => '0107', 'departamento_codigo' => '01'],
            ['codigo' => '010703', 'descripcion' => 'Cumba', 'provincia_codigo' => '0107', 'departamento_codigo' => '01'],
            ['codigo' => '010704', 'descripcion' => 'El Milagro', 'provincia_codigo' => '0107', 'departamento_codigo' => '01'],
            ['codigo' => '010705', 'descripcion' => 'Jamalca', 'provincia_codigo' => '0107', 'departamento_codigo' => '01'],
            ['codigo' => '010706', 'descripcion' => 'Lonya Grande', 'provincia_codigo' => '0107', 'departamento_codigo' => '01'],
            ['codigo' => '010707', 'descripcion' => 'Yamón', 'provincia_codigo' => '0107', 'departamento_codigo' => '01'],
        ]);

        // ===============================
        // ÁNCASH - HUARAZ (0201)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020101', 'descripcion' => 'Huaraz', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020102', 'descripcion' => 'Cochabamba', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020103', 'descripcion' => 'Colcabamba', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020104', 'descripcion' => 'Huanchay', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020105', 'descripcion' => 'Independencia', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020106', 'descripcion' => 'Jangas', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020107', 'descripcion' => 'La Libertad', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020108', 'descripcion' => 'Olleros', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020109', 'descripcion' => 'Pampas Grande', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020110', 'descripcion' => 'Pariacoto', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020111', 'descripcion' => 'Pira', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
            ['codigo' => '020112', 'descripcion' => 'Tarica', 'provincia_codigo' => '0201', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - AIJA (0202)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020201', 'descripcion' => 'Aija', 'provincia_codigo' => '0202', 'departamento_codigo' => '02'],
            ['codigo' => '020202', 'descripcion' => 'Coris', 'provincia_codigo' => '0202', 'departamento_codigo' => '02'],
            ['codigo' => '020203', 'descripcion' => 'Huacllán', 'provincia_codigo' => '0202', 'departamento_codigo' => '02'],
            ['codigo' => '020204', 'descripcion' => 'La Merced', 'provincia_codigo' => '0202', 'departamento_codigo' => '02'],
            ['codigo' => '020205', 'descripcion' => 'Succha', 'provincia_codigo' => '0202', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - ANTONIO RAYMONDI (0203)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020301', 'descripcion' => 'Llamellín', 'provincia_codigo' => '0203', 'departamento_codigo' => '02'],
            ['codigo' => '020302', 'descripcion' => 'Aczo', 'provincia_codigo' => '0203', 'departamento_codigo' => '02'],
            ['codigo' => '020303', 'descripcion' => 'Chaccho', 'provincia_codigo' => '0203', 'departamento_codigo' => '02'],
            ['codigo' => '020304', 'descripcion' => 'Chingas', 'provincia_codigo' => '0203', 'departamento_codigo' => '02'],
            ['codigo' => '020305', 'descripcion' => 'Mirgas', 'provincia_codigo' => '0203', 'departamento_codigo' => '02'],
            ['codigo' => '020306', 'descripcion' => 'San Juan de Rontoy', 'provincia_codigo' => '0203', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - ASUNCIÓN (0204)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020401', 'descripcion' => 'Chacas', 'provincia_codigo' => '0204', 'departamento_codigo' => '02'],
            ['codigo' => '020402', 'descripcion' => 'Acochaca', 'provincia_codigo' => '0204', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - BOLOGNESI (0205)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020501', 'descripcion' => 'Chiquián', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020502', 'descripcion' => 'Abelardo Pardo Lezameta', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020503', 'descripcion' => 'Antonio Raymondi', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020504', 'descripcion' => 'Aquia', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020505', 'descripcion' => 'Cajacay', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020506', 'descripcion' => 'Canis', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020507', 'descripcion' => 'Colquioc', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020508', 'descripcion' => 'Huallanca', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020509', 'descripcion' => 'Huasta', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020510', 'descripcion' => 'Huayllacayán', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020511', 'descripcion' => 'La Primavera', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020512', 'descripcion' => 'Mangas', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020513', 'descripcion' => 'Pacllón', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020514', 'descripcion' => 'San Miguel de Corpanqui', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
            ['codigo' => '020515', 'descripcion' => 'Ticllos', 'provincia_codigo' => '0205', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - CARHUAZ (0206)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020601', 'descripcion' => 'Carhuaz', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020602', 'descripcion' => 'Acopampa', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020603', 'descripcion' => 'Amashca', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020604', 'descripcion' => 'Anta', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020605', 'descripcion' => 'Ataquero', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020606', 'descripcion' => 'Marcara', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020607', 'descripcion' => 'Pariahuanca', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020608', 'descripcion' => 'San Miguel de Aco', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020609', 'descripcion' => 'Shilla', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020610', 'descripcion' => 'Tinco', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
            ['codigo' => '020611', 'descripcion' => 'Yungar', 'provincia_codigo' => '0206', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - CARLOS FERMÍN FITZCARRALD (0207)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020701', 'descripcion' => 'San Luis', 'provincia_codigo' => '0207', 'departamento_codigo' => '02'],
            ['codigo' => '020702', 'descripcion' => 'San Nicolás', 'provincia_codigo' => '0207', 'departamento_codigo' => '02'],
            ['codigo' => '020703', 'descripcion' => 'Yauya', 'provincia_codigo' => '0207', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - CASMA (0208)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020801', 'descripcion' => 'Casma', 'provincia_codigo' => '0208', 'departamento_codigo' => '02'],
            ['codigo' => '020802', 'descripcion' => 'Buena Vista Alta', 'provincia_codigo' => '0208', 'departamento_codigo' => '02'],
            ['codigo' => '020803', 'descripcion' => 'Comandante Noel', 'provincia_codigo' => '0208', 'departamento_codigo' => '02'],
            ['codigo' => '020804', 'descripcion' => 'Yaután', 'provincia_codigo' => '0208', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - CORONGO (0209)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '020901', 'descripcion' => 'Corongo', 'provincia_codigo' => '0209', 'departamento_codigo' => '02'],
            ['codigo' => '020902', 'descripcion' => 'Aco', 'provincia_codigo' => '0209', 'departamento_codigo' => '02'],
            ['codigo' => '020903', 'descripcion' => 'Bambas', 'provincia_codigo' => '0209', 'departamento_codigo' => '02'],
            ['codigo' => '020904', 'descripcion' => 'Cusca', 'provincia_codigo' => '0209', 'departamento_codigo' => '02'],
            ['codigo' => '020905', 'descripcion' => 'La Pampa', 'provincia_codigo' => '0209', 'departamento_codigo' => '02'],
            ['codigo' => '020906', 'descripcion' => 'Yanac', 'provincia_codigo' => '0209', 'departamento_codigo' => '02'],
            ['codigo' => '020907', 'descripcion' => 'Yupán', 'provincia_codigo' => '0209', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - HUARI (0210)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021001', 'descripcion' => 'Huari', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021002', 'descripcion' => 'Anra', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021003', 'descripcion' => 'Cajay', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021004', 'descripcion' => 'Chavín de Huántar', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021005', 'descripcion' => 'Huacachi', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021006', 'descripcion' => 'Huacchis', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021007', 'descripcion' => 'Huachis', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021008', 'descripcion' => 'Huántar', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021009', 'descripcion' => 'Masín', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021010', 'descripcion' => 'Paucas', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021011', 'descripcion' => 'Ponto', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021012', 'descripcion' => 'Rahuapampa', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021013', 'descripcion' => 'Rapayán', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021014', 'descripcion' => 'San Marcos', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021015', 'descripcion' => 'San Pedro de Chaná', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
            ['codigo' => '021016', 'descripcion' => 'Uco', 'provincia_codigo' => '0210', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - HUARMEY (0211)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021101', 'descripcion' => 'Huarmey', 'provincia_codigo' => '0211', 'departamento_codigo' => '02'],
            ['codigo' => '021102', 'descripcion' => 'Cochapeti', 'provincia_codigo' => '0211', 'departamento_codigo' => '02'],
            ['codigo' => '021103', 'descripcion' => 'Culebras', 'provincia_codigo' => '0211', 'departamento_codigo' => '02'],
            ['codigo' => '021104', 'descripcion' => 'Huayán', 'provincia_codigo' => '0211', 'departamento_codigo' => '02'],
            ['codigo' => '021105', 'descripcion' => 'Malvas', 'provincia_codigo' => '0211', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - HUAYLAS (0212)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021201', 'descripcion' => 'Caraz', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021202', 'descripcion' => 'Huallanca', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021203', 'descripcion' => 'Huata', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021204', 'descripcion' => 'Huaylas', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021205', 'descripcion' => 'Mato', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021206', 'descripcion' => 'Pamparomás', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021207', 'descripcion' => 'Pueblo Libre', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021208', 'descripcion' => 'Santa Cruz', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021209', 'descripcion' => 'Santo Toribio', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
            ['codigo' => '021210', 'descripcion' => 'Yuracmarca', 'provincia_codigo' => '0212', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - MARISCAL LUZURIAGA (0213)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021301', 'descripcion' => 'Piscobamba', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
            ['codigo' => '021302', 'descripcion' => 'Casca', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
            ['codigo' => '021303', 'descripcion' => 'Eleazar Guzmán Barrón', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
            ['codigo' => '021304', 'descripcion' => 'Fidel Olivas Escudero', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
            ['codigo' => '021305', 'descripcion' => 'Llama', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
            ['codigo' => '021306', 'descripcion' => 'Llumpa', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
            ['codigo' => '021307', 'descripcion' => 'Lucma', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
            ['codigo' => '021308', 'descripcion' => 'Musga', 'provincia_codigo' => '0213', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - OCROS (0214)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021401', 'descripcion' => 'Ocros', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021402', 'descripcion' => 'Acas', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021403', 'descripcion' => 'Cajamarquilla', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021404', 'descripcion' => 'Carhuapampa', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021405', 'descripcion' => 'Cochas', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021406', 'descripcion' => 'Congas', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021407', 'descripcion' => 'Llipa', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021408', 'descripcion' => 'San Cristóbal de Raján', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021409', 'descripcion' => 'San Pedro', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
            ['codigo' => '021410', 'descripcion' => 'Santiago de Chilcas', 'provincia_codigo' => '0214', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - PALLASCA (0215)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021501', 'descripcion' => 'Cabana', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021502', 'descripcion' => 'Bolognesi', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021503', 'descripcion' => 'Conchucos', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021504', 'descripcion' => 'Huacaschuque', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021505', 'descripcion' => 'Huandoval', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021506', 'descripcion' => 'Lacabamba', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021507', 'descripcion' => 'Llapo', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021508', 'descripcion' => 'Pallasca', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021509', 'descripcion' => 'Pampas', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021510', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
            ['codigo' => '021511', 'descripcion' => 'Tauca', 'provincia_codigo' => '0215', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - POMABAMBA (0216)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021601', 'descripcion' => 'Pomabamba', 'provincia_codigo' => '0216', 'departamento_codigo' => '02'],
            ['codigo' => '021602', 'descripcion' => 'Huayllán', 'provincia_codigo' => '0216', 'departamento_codigo' => '02'],
            ['codigo' => '021603', 'descripcion' => 'Parobamba', 'provincia_codigo' => '0216', 'departamento_codigo' => '02'],
            ['codigo' => '021604', 'descripcion' => 'Quinuabamba', 'provincia_codigo' => '0216', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - RECUAY (0217)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021701', 'descripcion' => 'Recuay', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021702', 'descripcion' => 'Catac', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021703', 'descripcion' => 'Cotaparaco', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021704', 'descripcion' => 'Huayllapampa', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021705', 'descripcion' => 'Llacllín', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021706', 'descripcion' => 'Marca', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021707', 'descripcion' => 'Pampas Chico', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021708', 'descripcion' => 'Pararín', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021709', 'descripcion' => 'Tapacocha', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
            ['codigo' => '021710', 'descripcion' => 'Ticapampa', 'provincia_codigo' => '0217', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - SANTA (0218)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021801', 'descripcion' => 'Chimbote', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021802', 'descripcion' => 'Cáceres del Perú', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021803', 'descripcion' => 'Coishco', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021804', 'descripcion' => 'Macate', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021805', 'descripcion' => 'Moro', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021806', 'descripcion' => 'Nepeña', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021807', 'descripcion' => 'Samanco', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021808', 'descripcion' => 'Santa', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
            ['codigo' => '021809', 'descripcion' => 'Nuevo Chimbote', 'provincia_codigo' => '0218', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - SIHUAS (0219)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '021901', 'descripcion' => 'Sihuas', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021902', 'descripcion' => 'Acobamba', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021903', 'descripcion' => 'Alfonso Ugarte', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021904', 'descripcion' => 'Cashapampa', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021905', 'descripcion' => 'Chingalpo', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021906', 'descripcion' => 'Huayllabamba', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021907', 'descripcion' => 'Quiches', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021908', 'descripcion' => 'Ragash', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021909', 'descripcion' => 'San Juan', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
            ['codigo' => '021910', 'descripcion' => 'Sicsibamba', 'provincia_codigo' => '0219', 'departamento_codigo' => '02'],
        ]);

        // ===============================
        // ÁNCASH - YUNGAY (0220)
        // ===============================
        $districts = array_merge($districts, [
            ['codigo' => '022001', 'descripcion' => 'Yungay', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
            ['codigo' => '022002', 'descripcion' => 'Cascapara', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
            ['codigo' => '022003', 'descripcion' => 'Mancos', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
            ['codigo' => '022004', 'descripcion' => 'Matacoto', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
            ['codigo' => '022005', 'descripcion' => 'Quillo', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
            ['codigo' => '022006', 'descripcion' => 'Ranrahirca', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
            ['codigo' => '022007', 'descripcion' => 'Shupluy', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
            ['codigo' => '022008', 'descripcion' => 'Yanama', 'provincia_codigo' => '0220', 'departamento_codigo' => '02'],
        ]);

        // ==================================================
        // APURÍMAC (03)
        // ==================================================

        // -------------------------------
        // ABANCAY (0301)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '030101', 'descripcion' => 'Abancay', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030102', 'descripcion' => 'Chacoche', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030103', 'descripcion' => 'Circa', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030104', 'descripcion' => 'Curahuasi', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030105', 'descripcion' => 'Huanipaca', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030106', 'descripcion' => 'Lambrama', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030107', 'descripcion' => 'Pichirhua', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030108', 'descripcion' => 'San Pedro de Cachora', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
            ['codigo' => '030109', 'descripcion' => 'Tamburco', 'provincia_codigo' => '0301', 'departamento_codigo' => '03'],
        ]);

        // -------------------------------
        // ANDAHUAYLAS (0302)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '030201', 'descripcion' => 'Andahuaylas', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030202', 'descripcion' => 'Andarapa', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030203', 'descripcion' => 'Chiara', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030204', 'descripcion' => 'Huancarama', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030205', 'descripcion' => 'Huancaray', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030206', 'descripcion' => 'Huayana', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030207', 'descripcion' => 'Kishuara', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030208', 'descripcion' => 'Pacobamba', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030209', 'descripcion' => 'Pacucha', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030210', 'descripcion' => 'Pampachiri', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030211', 'descripcion' => 'Pomacocha', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030212', 'descripcion' => 'San Antonio de Cachi', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030213', 'descripcion' => 'San Jerónimo', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030214', 'descripcion' => 'San Miguel de Chaccrampa', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030215', 'descripcion' => 'Santa María de Chicmo', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030216', 'descripcion' => 'Talavera', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030217', 'descripcion' => 'Tumay Huaraca', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030218', 'descripcion' => 'Turpo', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030219', 'descripcion' => 'Kaquiabamba', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
            ['codigo' => '030220', 'descripcion' => 'José María Arguedas', 'provincia_codigo' => '0302', 'departamento_codigo' => '03'],
        ]);

        // -------------------------------
        // ANTABAMBA (0303)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '030301', 'descripcion' => 'Antabamba', 'provincia_codigo' => '0303', 'departamento_codigo' => '03'],
            ['codigo' => '030302', 'descripcion' => 'El Oro', 'provincia_codigo' => '0303', 'departamento_codigo' => '03'],
            ['codigo' => '030303', 'descripcion' => 'Huaquirca', 'provincia_codigo' => '0303', 'departamento_codigo' => '03'],
            ['codigo' => '030304', 'descripcion' => 'Juan Espinoza Medrano', 'provincia_codigo' => '0303', 'departamento_codigo' => '03'],
            ['codigo' => '030305', 'descripcion' => 'Oropesa', 'provincia_codigo' => '0303', 'departamento_codigo' => '03'],
            ['codigo' => '030306', 'descripcion' => 'Pachaconas', 'provincia_codigo' => '0303', 'departamento_codigo' => '03'],
            ['codigo' => '030307', 'descripcion' => 'Sabaino', 'provincia_codigo' => '0303', 'departamento_codigo' => '03'],
        ]);

        // -------------------------------
        // AYMARAES (0304)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '030401', 'descripcion' => 'Chalhuanca', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030402', 'descripcion' => 'Capaya', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030403', 'descripcion' => 'Caraybamba', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030404', 'descripcion' => 'Chapimarca', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030405', 'descripcion' => 'Colcabamba', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030406', 'descripcion' => 'Cotaruse', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030407', 'descripcion' => 'Ihuayllo', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030408', 'descripcion' => 'Justo Apu Sahuaraura', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030409', 'descripcion' => 'Lucre', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030410', 'descripcion' => 'Pocohuanca', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030411', 'descripcion' => 'San Juan de Chacña', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030412', 'descripcion' => 'Sañayca', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030413', 'descripcion' => 'Soraya', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030414', 'descripcion' => 'Tapairihua', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030415', 'descripcion' => 'Tintay', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030416', 'descripcion' => 'Toraya', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
            ['codigo' => '030417', 'descripcion' => 'Yanaca', 'provincia_codigo' => '0304', 'departamento_codigo' => '03'],
        ]);

        // -------------------------------
        // COTABAMBAS (0305)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '030501', 'descripcion' => 'Tambobamba', 'provincia_codigo' => '0305', 'departamento_codigo' => '03'],
            ['codigo' => '030502', 'descripcion' => 'Cotabambas', 'provincia_codigo' => '0305', 'departamento_codigo' => '03'],
            ['codigo' => '030503', 'descripcion' => 'Coyllurqui', 'provincia_codigo' => '0305', 'departamento_codigo' => '03'],
            ['codigo' => '030504', 'descripcion' => 'Haquira', 'provincia_codigo' => '0305', 'departamento_codigo' => '03'],
            ['codigo' => '030505', 'descripcion' => 'Mara', 'provincia_codigo' => '0305', 'departamento_codigo' => '03'],
            ['codigo' => '030506', 'descripcion' => 'Challhuahuacho', 'provincia_codigo' => '0305', 'departamento_codigo' => '03'],
        ]);

        // -------------------------------
        // CHINCHEROS (0306)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '030601', 'descripcion' => 'Chincheros', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
            ['codigo' => '030602', 'descripcion' => 'Anco-Huallo', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
            ['codigo' => '030603', 'descripcion' => 'Cocharcas', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
            ['codigo' => '030604', 'descripcion' => 'Huaccana', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
            ['codigo' => '030605', 'descripcion' => 'Ocobamba', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
            ['codigo' => '030606', 'descripcion' => 'Ongoy', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
            ['codigo' => '030607', 'descripcion' => 'Uranmarca', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
            ['codigo' => '030608', 'descripcion' => 'Ranracancha', 'provincia_codigo' => '0306', 'departamento_codigo' => '03'],
        ]);

        // -------------------------------
        // GRAU (0307)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '030701', 'descripcion' => 'Chuquibambilla', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030702', 'descripcion' => 'Curpahuasi', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030703', 'descripcion' => 'Gamarra', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030704', 'descripcion' => 'Huayllati', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030705', 'descripcion' => 'Mamara', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030706', 'descripcion' => 'Micaela Bastidas', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030707', 'descripcion' => 'Pataypampa', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030708', 'descripcion' => 'Progreso', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030709', 'descripcion' => 'San Antonio', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030710', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030711', 'descripcion' => 'Turpay', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030712', 'descripcion' => 'Vilcabamba', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030713', 'descripcion' => 'Virundo', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
            ['codigo' => '030714', 'descripcion' => 'Curasco', 'provincia_codigo' => '0307', 'departamento_codigo' => '03'],
        ]);

        // ==================================================
        // AREQUIPA (04)
        // ==================================================

        // -------------------------------
        // AREQUIPA (0401)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040101', 'descripcion' => 'Arequipa', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040102', 'descripcion' => 'Alto Selva Alegre', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040103', 'descripcion' => 'Cayma', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040104', 'descripcion' => 'Cerro Colorado', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040105', 'descripcion' => 'Characato', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040106', 'descripcion' => 'Chiguata', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040107', 'descripcion' => 'Jacobo Hunter', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040108', 'descripcion' => 'La Joya', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040109', 'descripcion' => 'Mariano Melgar', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040110', 'descripcion' => 'Miraflores', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040111', 'descripcion' => 'Mollebaya', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040112', 'descripcion' => 'Paucarpata', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040113', 'descripcion' => 'Pocsi', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040114', 'descripcion' => 'Polobaya', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040115', 'descripcion' => 'Quequeña', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040116', 'descripcion' => 'Sabandía', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040117', 'descripcion' => 'Sachaca', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040118', 'descripcion' => 'San Juan de Siguas', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040119', 'descripcion' => 'San Juan de Tarucani', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040120', 'descripcion' => 'Santa Isabel de Siguas', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040121', 'descripcion' => 'Santa Rita de Siguas', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040122', 'descripcion' => 'Socabaya', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040123', 'descripcion' => 'Tiabaya', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040124', 'descripcion' => 'Uchumayo', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040125', 'descripcion' => 'Vítor', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040126', 'descripcion' => 'Yanahuara', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040127', 'descripcion' => 'Yarabamba', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040128', 'descripcion' => 'Yura', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
            ['codigo' => '040129', 'descripcion' => 'José Luis Bustamante y Rivero', 'provincia_codigo' => '0401', 'departamento_codigo' => '04'],
        ]);

        // -------------------------------
        // CAMANÁ (0402)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040201', 'descripcion' => 'Camaná', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
            ['codigo' => '040202', 'descripcion' => 'José María Quimper', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
            ['codigo' => '040203', 'descripcion' => 'Mariano Nicolás Valcárcel', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
            ['codigo' => '040204', 'descripcion' => 'Mariscal Cáceres', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
            ['codigo' => '040205', 'descripcion' => 'Nicolás de Piérola', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
            ['codigo' => '040206', 'descripcion' => 'Ocoña', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
            ['codigo' => '040207', 'descripcion' => 'Quilca', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
            ['codigo' => '040208', 'descripcion' => 'Samuel Pastor', 'provincia_codigo' => '0402', 'departamento_codigo' => '04'],
        ]);

        // -------------------------------
        // CARAVELÍ (0403)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040301', 'descripcion' => 'Caravelí', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040302', 'descripcion' => 'Acarí', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040303', 'descripcion' => 'Atico', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040304', 'descripcion' => 'Atiquipa', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040305', 'descripcion' => 'Bella Unión', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040306', 'descripcion' => 'Cahuacho', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040307', 'descripcion' => 'Chala', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040308', 'descripcion' => 'Chaparra', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040309', 'descripcion' => 'Huanuhuanu', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040310', 'descripcion' => 'Jaquí', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040311', 'descripcion' => 'Lomas', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040312', 'descripcion' => 'Quicacha', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
            ['codigo' => '040313', 'descripcion' => 'Yauca', 'provincia_codigo' => '0403', 'departamento_codigo' => '04'],
        ]);

        // -------------------------------
        // CASTILLA (0404)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040401', 'descripcion' => 'Aplao', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040402', 'descripcion' => 'Andagua', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040403', 'descripcion' => 'Ayo', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040404', 'descripcion' => 'Chachas', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040405', 'descripcion' => 'Chilcaymarca', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040406', 'descripcion' => 'Choco', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040407', 'descripcion' => 'Huancarqui', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040408', 'descripcion' => 'Machaguay', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040409', 'descripcion' => 'Orcopampa', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040410', 'descripcion' => 'Pampacolca', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040411', 'descripcion' => 'Tipán', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040412', 'descripcion' => 'Uñón', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040413', 'descripcion' => 'Uraca', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
            ['codigo' => '040414', 'descripcion' => 'Viraco', 'provincia_codigo' => '0404', 'departamento_codigo' => '04'],
        ]);

        // -------------------------------
        // CAYLLOMA (0405)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040501', 'descripcion' => 'Chivay', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040502', 'descripcion' => 'Achoma', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040503', 'descripcion' => 'Cabanaconde', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040504', 'descripcion' => 'Callalli', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040505', 'descripcion' => 'Caylloma', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040506', 'descripcion' => 'Coporaque', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040507', 'descripcion' => 'Huambo', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040508', 'descripcion' => 'Huanca', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040509', 'descripcion' => 'Ichupampa', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040510', 'descripcion' => 'Lari', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040511', 'descripcion' => 'Lluta', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040512', 'descripcion' => 'Maca', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040513', 'descripcion' => 'Madrigal', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040514', 'descripcion' => 'San Antonio de Chuca', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040515', 'descripcion' => 'Sibayo', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040516', 'descripcion' => 'Tapay', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040517', 'descripcion' => 'Tisco', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040518', 'descripcion' => 'Tuti', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040519', 'descripcion' => 'Yanque', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
            ['codigo' => '040520', 'descripcion' => 'Majes', 'provincia_codigo' => '0405', 'departamento_codigo' => '04'],
        ]);

        // -------------------------------
        // CONDESUYOS (0406)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040601', 'descripcion' => 'Chuquibamba', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
            ['codigo' => '040602', 'descripcion' => 'Andaray', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
            ['codigo' => '040603', 'descripcion' => 'Cayarani', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
            ['codigo' => '040604', 'descripcion' => 'Chichas', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
            ['codigo' => '040605', 'descripcion' => 'Iray', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
            ['codigo' => '040606', 'descripcion' => 'Río Grande', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
            ['codigo' => '040607', 'descripcion' => 'Salamanca', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
            ['codigo' => '040608', 'descripcion' => 'Yanaquihua', 'provincia_codigo' => '0406', 'departamento_codigo' => '04'],
        ]);

        // -------------------------------
        // ISLAY (0407)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040701', 'descripcion' => 'Mollendo', 'provincia_codigo' => '0407', 'departamento_codigo' => '04'],
            ['codigo' => '040702', 'descripcion' => 'Cocachacra', 'provincia_codigo' => '0407', 'departamento_codigo' => '04'],
            ['codigo' => '040703', 'descripcion' => 'Dean Valdivia', 'provincia_codigo' => '0407', 'departamento_codigo' => '04'],
            ['codigo' => '040704', 'descripcion' => 'Islay', 'provincia_codigo' => '0407', 'departamento_codigo' => '04'],
            ['codigo' => '040705', 'descripcion' => 'Mejía', 'provincia_codigo' => '0407', 'departamento_codigo' => '04'],
            ['codigo' => '040706', 'descripcion' => 'Punta de Bombón', 'provincia_codigo' => '0407', 'departamento_codigo' => '04'],
        ]);

        // -------------------------------
        // LA UNIÓN (0408)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '040801', 'descripcion' => 'Cotahuasi', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040802', 'descripcion' => 'Alca', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040803', 'descripcion' => 'Charcana', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040804', 'descripcion' => 'Huaynacotas', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040805', 'descripcion' => 'Pampamarca', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040806', 'descripcion' => 'Puyca', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040807', 'descripcion' => 'Quechualla', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040808', 'descripcion' => 'Sayla', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040809', 'descripcion' => 'Tauría', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040810', 'descripcion' => 'Tomepampa', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
            ['codigo' => '040811', 'descripcion' => 'Toro', 'provincia_codigo' => '0408', 'departamento_codigo' => '04'],
        ]);

        // ==================================================
        // AYACUCHO (05)
        // ==================================================

        // -------------------------------
        // HUAMANGA (0501)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050101', 'descripcion' => 'Ayacucho', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050102', 'descripcion' => 'Acocro', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050103', 'descripcion' => 'Acos Vinchos', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050104', 'descripcion' => 'Carmen Alto', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050105', 'descripcion' => 'Chiara', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050106', 'descripcion' => 'Ocros', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050107', 'descripcion' => 'Pacaycasa', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050108', 'descripcion' => 'Quinua', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050109', 'descripcion' => 'San José de Ticllas', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050110', 'descripcion' => 'San Juan Bautista', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050111', 'descripcion' => 'Santiago de Pischa', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050112', 'descripcion' => 'Socos', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050113', 'descripcion' => 'Tambillo', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050114', 'descripcion' => 'Vinchos', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050115', 'descripcion' => 'Jesús Nazareno', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
            ['codigo' => '050116', 'descripcion' => 'Andrés Avelino Cáceres Dorregaray', 'provincia_codigo' => '0501', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // CANGALLO (0502)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050201', 'descripcion' => 'Cangallo', 'provincia_codigo' => '0502', 'departamento_codigo' => '05'],
            ['codigo' => '050202', 'descripcion' => 'Chuschi', 'provincia_codigo' => '0502', 'departamento_codigo' => '05'],
            ['codigo' => '050203', 'descripcion' => 'Los Morochucos', 'provincia_codigo' => '0502', 'departamento_codigo' => '05'],
            ['codigo' => '050204', 'descripcion' => 'María Parado de Bellido', 'provincia_codigo' => '0502', 'departamento_codigo' => '05'],
            ['codigo' => '050205', 'descripcion' => 'Paras', 'provincia_codigo' => '0502', 'departamento_codigo' => '05'],
            ['codigo' => '050206', 'descripcion' => 'Totos', 'provincia_codigo' => '0502', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // HUANCA SANCOS (0503)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050301', 'descripcion' => 'Sancos', 'provincia_codigo' => '0503', 'departamento_codigo' => '05'],
            ['codigo' => '050302', 'descripcion' => 'Carapo', 'provincia_codigo' => '0503', 'departamento_codigo' => '05'],
            ['codigo' => '050303', 'descripcion' => 'Sacsamarca', 'provincia_codigo' => '0503', 'departamento_codigo' => '05'],
            ['codigo' => '050304', 'descripcion' => 'Santiago de Lucanamarca', 'provincia_codigo' => '0503', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // HUANTA (0504)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050401', 'descripcion' => 'Huanta', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050402', 'descripcion' => 'Ayahuanco', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050403', 'descripcion' => 'Huamanguilla', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050404', 'descripcion' => 'Iguain', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050405', 'descripcion' => 'Llochegua', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050406', 'descripcion' => 'Luricocha', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050407', 'descripcion' => 'Santillana', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050408', 'descripcion' => 'Sivia', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
            ['codigo' => '050409', 'descripcion' => 'Canayre', 'provincia_codigo' => '0504', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // LA MAR (0505)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050501', 'descripcion' => 'San Miguel', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
            ['codigo' => '050502', 'descripcion' => 'Anco', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
            ['codigo' => '050503', 'descripcion' => 'Ayna', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
            ['codigo' => '050504', 'descripcion' => 'Chilcas', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
            ['codigo' => '050505', 'descripcion' => 'Chungui', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
            ['codigo' => '050506', 'descripcion' => 'Luis Carranza', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
            ['codigo' => '050507', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
            ['codigo' => '050508', 'descripcion' => 'Tambo', 'provincia_codigo' => '0505', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // LUCANAS (0506)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050601', 'descripcion' => 'Puquio', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050602', 'descripcion' => 'Aucara', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050603', 'descripcion' => 'Cabana', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050604', 'descripcion' => 'Carmen Salcedo', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050605', 'descripcion' => 'Chaviña', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050606', 'descripcion' => 'Chipao', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050607', 'descripcion' => 'Huac-Huas', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050608', 'descripcion' => 'Laramate', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050609', 'descripcion' => 'Leoncio Prado', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050610', 'descripcion' => 'Llauta', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050611', 'descripcion' => 'Lucanas', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050612', 'descripcion' => 'Ocaña', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050613', 'descripcion' => 'Otoca', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050614', 'descripcion' => 'Saisa', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050615', 'descripcion' => 'San Cristóbal', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050616', 'descripcion' => 'San Juan', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050617', 'descripcion' => 'San Pedro', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050618', 'descripcion' => 'San Pedro de Palco', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050619', 'descripcion' => 'Sancos', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050620', 'descripcion' => 'Santa Ana de Huaycahuacho', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
            ['codigo' => '050621', 'descripcion' => 'Santa Lucía', 'provincia_codigo' => '0506', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // PARINACOCHAS (0507)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050701', 'descripcion' => 'Coracora', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
            ['codigo' => '050702', 'descripcion' => 'Chumpi', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
            ['codigo' => '050703', 'descripcion' => 'Coronel Castañeda', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
            ['codigo' => '050704', 'descripcion' => 'Pacapausa', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
            ['codigo' => '050705', 'descripcion' => 'Pullo', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
            ['codigo' => '050706', 'descripcion' => 'Puyusca', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
            ['codigo' => '050707', 'descripcion' => 'San Francisco de Ravacayco', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
            ['codigo' => '050708', 'descripcion' => 'Upahuacho', 'provincia_codigo' => '0507', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // PÁUCAR DEL SARA SARA (0508)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050801', 'descripcion' => 'Pausa', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050802', 'descripcion' => 'Colta', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050803', 'descripcion' => 'Corculla', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050804', 'descripcion' => 'Lampa', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050805', 'descripcion' => 'Marcabamba', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050806', 'descripcion' => 'Oyolo', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050807', 'descripcion' => 'Pararca', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050808', 'descripcion' => 'San Javier de Alpabamba', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050809', 'descripcion' => 'San José de Ushua', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
            ['codigo' => '050810', 'descripcion' => 'Sara Sara', 'provincia_codigo' => '0508', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // SUCRE (0509)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '050901', 'descripcion' => 'Querobamba', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050902', 'descripcion' => 'Belén', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050903', 'descripcion' => 'Chalcos', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050904', 'descripcion' => 'Chilcayoc', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050905', 'descripcion' => 'Huacaña', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050906', 'descripcion' => 'Morcolla', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050907', 'descripcion' => 'Paico', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050908', 'descripcion' => 'San Pedro de Larcay', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050909', 'descripcion' => 'San Salvador de Quije', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050910', 'descripcion' => 'Santiago de Paucaray', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
            ['codigo' => '050911', 'descripcion' => 'Soras', 'provincia_codigo' => '0509', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // VÍCTOR FAJARDO (0510)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '051001', 'descripcion' => 'Huancapi', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051002', 'descripcion' => 'Alcamenca', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051003', 'descripcion' => 'Apongo', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051004', 'descripcion' => 'Asquipata', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051005', 'descripcion' => 'Canaria', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051006', 'descripcion' => 'Cayara', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051007', 'descripcion' => 'Colca', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051008', 'descripcion' => 'Huamanquiquia', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051009', 'descripcion' => 'Huancaraylla', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051010', 'descripcion' => 'Huaya', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051011', 'descripcion' => 'Sarhua', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
            ['codigo' => '051012', 'descripcion' => 'Vilcanchos', 'provincia_codigo' => '0510', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // VILCAS HUAMÁN (0511)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '051101', 'descripcion' => 'Vilcas Huamán', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
            ['codigo' => '051102', 'descripcion' => 'Accomarca', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
            ['codigo' => '051103', 'descripcion' => 'Carhuanca', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
            ['codigo' => '051104', 'descripcion' => 'Concepción', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
            ['codigo' => '051105', 'descripcion' => 'Huambalpa', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
            ['codigo' => '051106', 'descripcion' => 'Independencia', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
            ['codigo' => '051107', 'descripcion' => 'Saurama', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
            ['codigo' => '051108', 'descripcion' => 'Vischongo', 'provincia_codigo' => '0511', 'departamento_codigo' => '05'],
        ]);

        // -------------------------------
        // CAJAMARCA (0601)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060101', 'descripcion' => 'Cajamarca', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060102', 'descripcion' => 'Asunción', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060103', 'descripcion' => 'Chetilla', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060104', 'descripcion' => 'Cospán', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060105', 'descripcion' => 'Encañada', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060106', 'descripcion' => 'Jesús', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060107', 'descripcion' => 'Llacanora', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060108', 'descripcion' => 'Los Baños del Inca', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060109', 'descripcion' => 'Magdalena', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060110', 'descripcion' => 'Matara', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060111', 'descripcion' => 'Namora', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
            ['codigo' => '060112', 'descripcion' => 'San Juan', 'provincia_codigo' => '0601', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // CAJABAMBA (0602)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060201', 'descripcion' => 'Cajabamba', 'provincia_codigo' => '0602', 'departamento_codigo' => '06'],
            ['codigo' => '060202', 'descripcion' => 'Cachachi', 'provincia_codigo' => '0602', 'departamento_codigo' => '06'],
            ['codigo' => '060203', 'descripcion' => 'Condebamba', 'provincia_codigo' => '0602', 'departamento_codigo' => '06'],
            ['codigo' => '060204', 'descripcion' => 'Sitacocha', 'provincia_codigo' => '0602', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // CELENDÍN (0603)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060301', 'descripcion' => 'Celendín', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060302', 'descripcion' => 'Chumuch', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060303', 'descripcion' => 'Cortegana', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060304', 'descripcion' => 'Huasmin', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060305', 'descripcion' => 'Jorge Chávez', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060306', 'descripcion' => 'José Gálvez', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060307', 'descripcion' => 'Miguel Iglesias', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060308', 'descripcion' => 'Oxamarca', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060309', 'descripcion' => 'Sorochuco', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060310', 'descripcion' => 'Sucre', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060311', 'descripcion' => 'Utco', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
            ['codigo' => '060312', 'descripcion' => 'La Libertad de Pallán', 'provincia_codigo' => '0603', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // CHOTA (0604)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060401', 'descripcion' => 'Chota', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060402', 'descripcion' => 'Anguía', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060403', 'descripcion' => 'Chadín', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060404', 'descripcion' => 'Chiguirip', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060405', 'descripcion' => 'Chimban', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060406', 'descripcion' => 'Choropampa', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060407', 'descripcion' => 'Cochabamba', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060408', 'descripcion' => 'Conchán', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060409', 'descripcion' => 'Huambos', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060410', 'descripcion' => 'Lajas', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060411', 'descripcion' => 'Llama', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060412', 'descripcion' => 'Miracosta', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060413', 'descripcion' => 'Paccha', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060414', 'descripcion' => 'Pion', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060415', 'descripcion' => 'Querocoto', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060416', 'descripcion' => 'San Juan de Licupis', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060417', 'descripcion' => 'Tacabamba', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060418', 'descripcion' => 'Tocmoche', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
            ['codigo' => '060419', 'descripcion' => 'Chalamarca', 'provincia_codigo' => '0604', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // CONTUMAZÁ (0605)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060501', 'descripcion' => 'Contumazá', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
            ['codigo' => '060502', 'descripcion' => 'Chilete', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
            ['codigo' => '060503', 'descripcion' => 'Cupisnique', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
            ['codigo' => '060504', 'descripcion' => 'Guzmango', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
            ['codigo' => '060505', 'descripcion' => 'San Benito', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
            ['codigo' => '060506', 'descripcion' => 'Santa Cruz de Toledo', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
            ['codigo' => '060507', 'descripcion' => 'Tantarica', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
            ['codigo' => '060508', 'descripcion' => 'Yonan', 'provincia_codigo' => '0605', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // CUTERVO (0606)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060601', 'descripcion' => 'Cutervo', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060602', 'descripcion' => 'Callayuc', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060603', 'descripcion' => 'Choros', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060604', 'descripcion' => 'Cujillo', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060605', 'descripcion' => 'La Ramada', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060606', 'descripcion' => 'Pimpingos', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060607', 'descripcion' => 'Querocotillo', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060608', 'descripcion' => 'San Andrés de Cutervo', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060609', 'descripcion' => 'San Juan de Cutervo', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060610', 'descripcion' => 'San Luis de Lucma', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060611', 'descripcion' => 'Santa Cruz', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060612', 'descripcion' => 'Santo Domingo de la Capilla', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060613', 'descripcion' => 'Santo Tomás', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060614', 'descripcion' => 'Socota', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
            ['codigo' => '060615', 'descripcion' => 'Toribio Casanova', 'provincia_codigo' => '0606', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // HUALGAYOC (0607)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060701', 'descripcion' => 'Bambamarca', 'provincia_codigo' => '0607', 'departamento_codigo' => '06'],
            ['codigo' => '060702', 'descripcion' => 'Chugur', 'provincia_codigo' => '0607', 'departamento_codigo' => '06'],
            ['codigo' => '060703', 'descripcion' => 'Hualgayoc', 'provincia_codigo' => '0607', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // JAÉN (0608)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060801', 'descripcion' => 'Jaén', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060802', 'descripcion' => 'Bellavista', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060803', 'descripcion' => 'Chontali', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060804', 'descripcion' => 'Colasay', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060805', 'descripcion' => 'Huabal', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060806', 'descripcion' => 'Las Pirias', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060807', 'descripcion' => 'Pomahuaca', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060808', 'descripcion' => 'Pucará', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060809', 'descripcion' => 'Sallique', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060810', 'descripcion' => 'San Felipe', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060811', 'descripcion' => 'San José del Alto', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
            ['codigo' => '060812', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '0608', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // SAN IGNACIO (0609)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060901', 'descripcion' => 'San Ignacio', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060902', 'descripcion' => 'Chirinos', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060903', 'descripcion' => 'Huarango', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060904', 'descripcion' => 'La Coipa', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060905', 'descripcion' => 'Namballe', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060906', 'descripcion' => 'San José de Lourdes', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060907', 'descripcion' => 'Tabaconas', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // SAN IGNACIO (0609)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '060901', 'descripcion' => 'San Ignacio', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060902', 'descripcion' => 'Chirinos', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060903', 'descripcion' => 'Huarango', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060904', 'descripcion' => 'La Coipa', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060905', 'descripcion' => 'Namballe', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060906', 'descripcion' => 'San José de Lourdes', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
            ['codigo' => '060907', 'descripcion' => 'Tabaconas', 'provincia_codigo' => '0609', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // SAN MIGUEL (0611)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '061101', 'descripcion' => 'San Miguel', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061102', 'descripcion' => 'Bolívar', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061103', 'descripcion' => 'Calquis', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061104', 'descripcion' => 'Catilluc', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061105', 'descripcion' => 'El Prado', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061106', 'descripcion' => 'La Florida', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061107', 'descripcion' => 'Llapa', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061108', 'descripcion' => 'Nanchoc', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061109', 'descripcion' => 'Niepos', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061110', 'descripcion' => 'San Gregorio', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061111', 'descripcion' => 'San Silvestre de Cochan', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061112', 'descripcion' => 'Tongod', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
            ['codigo' => '061113', 'descripcion' => 'Unión Agua Blanca', 'provincia_codigo' => '0611', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // SAN PABLO (0612)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '061201', 'descripcion' => 'San Pablo', 'provincia_codigo' => '0612', 'departamento_codigo' => '06'],
            ['codigo' => '061202', 'descripcion' => 'San Bernardino', 'provincia_codigo' => '0612', 'departamento_codigo' => '06'],
            ['codigo' => '061203', 'descripcion' => 'San Luis', 'provincia_codigo' => '0612', 'departamento_codigo' => '06'],
            ['codigo' => '061204', 'descripcion' => 'Tumbadén', 'provincia_codigo' => '0612', 'departamento_codigo' => '06'],
        ]);

        // -------------------------------
        // SANTA CRUZ (0613)
        // -------------------------------
        $districts = array_merge($districts, [
            ['codigo' => '061301', 'descripcion' => 'Santa Cruz', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061302', 'descripcion' => 'Andabamba', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061303', 'descripcion' => 'Catache', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061304', 'descripcion' => 'Chancaybaños', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061305', 'descripcion' => 'La Esperanza', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061306', 'descripcion' => 'Ninabamba', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061307', 'descripcion' => 'Pulan', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061308', 'descripcion' => 'Saucepampa', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061309', 'descripcion' => 'Sexi', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061310', 'descripcion' => 'Uticyacu', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
            ['codigo' => '061311', 'descripcion' => 'Yauyucan', 'provincia_codigo' => '0613', 'departamento_codigo' => '06'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // CALLAO
            // ================================
            ['codigo' => '070101', 'descripcion' => 'Callao', 'provincia_codigo' => '0701', 'departamento_codigo' => '07'],
            ['codigo' => '070102', 'descripcion' => 'Bellavista', 'provincia_codigo' => '0701', 'departamento_codigo' => '07'],
            ['codigo' => '070103', 'descripcion' => 'Carmen de la Legua Reynoso', 'provincia_codigo' => '0701', 'departamento_codigo' => '07'],
            ['codigo' => '070104', 'descripcion' => 'La Perla', 'provincia_codigo' => '0701', 'departamento_codigo' => '07'],
            ['codigo' => '070105', 'descripcion' => 'La Punta', 'provincia_codigo' => '0701', 'departamento_codigo' => '07'],
            ['codigo' => '070106', 'descripcion' => 'Ventanilla', 'provincia_codigo' => '0701', 'departamento_codigo' => '07'],
            ['codigo' => '070107', 'descripcion' => 'Mi Perú', 'provincia_codigo' => '0701', 'departamento_codigo' => '07'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // CUSCO
            // ================================

            // PROVINCIA CUSCO (0801)
            ['codigo' => '080101', 'descripcion' => 'Cusco', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],
            ['codigo' => '080102', 'descripcion' => 'Ccorca', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],
            ['codigo' => '080103', 'descripcion' => 'Poroy', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],
            ['codigo' => '080104', 'descripcion' => 'San Jerónimo', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],
            ['codigo' => '080105', 'descripcion' => 'San Sebastián', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],
            ['codigo' => '080106', 'descripcion' => 'Santiago', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],
            ['codigo' => '080107', 'descripcion' => 'Saylla', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],
            ['codigo' => '080108', 'descripcion' => 'Wanchaq', 'provincia_codigo' => '0801', 'departamento_codigo' => '08'],

            // PROVINCIA ACOMAYO (0802)
            ['codigo' => '080201', 'descripcion' => 'Acomayo', 'provincia_codigo' => '0802', 'departamento_codigo' => '08'],
            ['codigo' => '080202', 'descripcion' => 'Acopia', 'provincia_codigo' => '0802', 'departamento_codigo' => '08'],
            ['codigo' => '080203', 'descripcion' => 'Acos', 'provincia_codigo' => '0802', 'departamento_codigo' => '08'],
            ['codigo' => '080204', 'descripcion' => 'Mosoc Llacta', 'provincia_codigo' => '0802', 'departamento_codigo' => '08'],
            ['codigo' => '080205', 'descripcion' => 'Pomacanchi', 'provincia_codigo' => '0802', 'departamento_codigo' => '08'],
            ['codigo' => '080206', 'descripcion' => 'Rondocan', 'provincia_codigo' => '0802', 'departamento_codigo' => '08'],
            ['codigo' => '080207', 'descripcion' => 'Sangarará', 'provincia_codigo' => '0802', 'departamento_codigo' => '08'],

            // PROVINCIA ANTA (0803)
            ['codigo' => '080301', 'descripcion' => 'Anta', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080302', 'descripcion' => 'Ancahuasi', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080303', 'descripcion' => 'Cachimayo', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080304', 'descripcion' => 'Chinchaypujio', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080305', 'descripcion' => 'Huarocondo', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080306', 'descripcion' => 'Limatambo', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080307', 'descripcion' => 'Mollepata', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080308', 'descripcion' => 'Pucyura', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],
            ['codigo' => '080309', 'descripcion' => 'Zurite', 'provincia_codigo' => '0803', 'departamento_codigo' => '08'],

            // PROVINCIA CALCA (0804)
            ['codigo' => '080401', 'descripcion' => 'Calca', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
            ['codigo' => '080402', 'descripcion' => 'Coya', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
            ['codigo' => '080403', 'descripcion' => 'Lamay', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
            ['codigo' => '080404', 'descripcion' => 'Lares', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
            ['codigo' => '080405', 'descripcion' => 'Pisac', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
            ['codigo' => '080406', 'descripcion' => 'San Salvador', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
            ['codigo' => '080407', 'descripcion' => 'Taray', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
            ['codigo' => '080408', 'descripcion' => 'Yanatile', 'provincia_codigo' => '0804', 'departamento_codigo' => '08'],
        ]);

        $districts = array_merge($districts, [
            // ================================
            // CUSCO CONTINUACIÓN
            // ================================

            // CANCHIS (0805)
            ['codigo' => '080501', 'descripcion' => 'Sicuani', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],
            ['codigo' => '080502', 'descripcion' => 'Checacupe', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],
            ['codigo' => '080503', 'descripcion' => 'Combapata', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],
            ['codigo' => '080504', 'descripcion' => 'Marangani', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],
            ['codigo' => '080505', 'descripcion' => 'Pitumarca', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],
            ['codigo' => '080506', 'descripcion' => 'San Pablo', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],
            ['codigo' => '080507', 'descripcion' => 'San Pedro', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],
            ['codigo' => '080508', 'descripcion' => 'Tinta', 'provincia_codigo' => '0805', 'departamento_codigo' => '08'],

            // CHUMBIVILCAS (0806)
            ['codigo' => '080601', 'descripcion' => 'Santo Tomás', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],
            ['codigo' => '080602', 'descripcion' => 'Capacmarca', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],
            ['codigo' => '080603', 'descripcion' => 'Chamaca', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],
            ['codigo' => '080604', 'descripcion' => 'Colquemarca', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],
            ['codigo' => '080605', 'descripcion' => 'Livitaca', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],
            ['codigo' => '080606', 'descripcion' => 'Llusco', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],
            ['codigo' => '080607', 'descripcion' => 'Quiñota', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],
            ['codigo' => '080608', 'descripcion' => 'Velille', 'provincia_codigo' => '0806', 'departamento_codigo' => '08'],

            // ESPINAR (0807)
            ['codigo' => '080701', 'descripcion' => 'Espinar', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],
            ['codigo' => '080702', 'descripcion' => 'Condoroma', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],
            ['codigo' => '080703', 'descripcion' => 'Coporaque', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],
            ['codigo' => '080704', 'descripcion' => 'Ocoruro', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],
            ['codigo' => '080705', 'descripcion' => 'Pallpata', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],
            ['codigo' => '080706', 'descripcion' => 'Pichigua', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],
            ['codigo' => '080707', 'descripcion' => 'Suyckutambo', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],
            ['codigo' => '080708', 'descripcion' => 'Alto Pichigua', 'provincia_codigo' => '0807', 'departamento_codigo' => '08'],

            // LA CONVENCIÓN (0808)
            ['codigo' => '080801', 'descripcion' => 'Santa Ana', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080802', 'descripcion' => 'Echarate', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080803', 'descripcion' => 'Huayopata', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080804', 'descripcion' => 'Maranura', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080805', 'descripcion' => 'Ocobamba', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080806', 'descripcion' => 'Quellouno', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080807', 'descripcion' => 'Kimbiri', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080808', 'descripcion' => 'Santa Teresa', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080809', 'descripcion' => 'Vilcabamba', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080810', 'descripcion' => 'Pichari', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080811', 'descripcion' => 'Inkawasi', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080812', 'descripcion' => 'Villa Virgen', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080813', 'descripcion' => 'Villa Kintiarina', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],
            ['codigo' => '080814', 'descripcion' => 'Megantoni', 'provincia_codigo' => '0808', 'departamento_codigo' => '08'],

            // PARURO (0809)
            ['codigo' => '080901', 'descripcion' => 'Paruro', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080902', 'descripcion' => 'Accha', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080903', 'descripcion' => 'Ccapi', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080904', 'descripcion' => 'Colcha', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080905', 'descripcion' => 'Huanoquite', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080906', 'descripcion' => 'Omacha', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080907', 'descripcion' => 'Paccaritambo', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080908', 'descripcion' => 'Pillpinto', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],
            ['codigo' => '080909', 'descripcion' => 'Yaurisque', 'provincia_codigo' => '0809', 'departamento_codigo' => '08'],

            // PAUCARTAMBO (0810)
            ['codigo' => '081001', 'descripcion' => 'Paucartambo', 'provincia_codigo' => '0810', 'departamento_codigo' => '08'],
            ['codigo' => '081002', 'descripcion' => 'Caicay', 'provincia_codigo' => '0810', 'departamento_codigo' => '08'],
            ['codigo' => '081003', 'descripcion' => 'Challabamba', 'provincia_codigo' => '0810', 'departamento_codigo' => '08'],
            ['codigo' => '081004', 'descripcion' => 'Colquepata', 'provincia_codigo' => '0810', 'departamento_codigo' => '08'],
            ['codigo' => '081005', 'descripcion' => 'Huancarani', 'provincia_codigo' => '0810', 'departamento_codigo' => '08'],
            ['codigo' => '081006', 'descripcion' => 'Kosñipata', 'provincia_codigo' => '0810', 'departamento_codigo' => '08'],

            // QUISPICANCHI (0811)
            ['codigo' => '081101', 'descripcion' => 'Urcos', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081102', 'descripcion' => 'Andahuaylillas', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081103', 'descripcion' => 'Camanti', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081104', 'descripcion' => 'Ccarhuayo', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081105', 'descripcion' => 'Ccatca', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081106', 'descripcion' => 'Cusipata', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081107', 'descripcion' => 'Huaro', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081108', 'descripcion' => 'Lucre', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081109', 'descripcion' => 'Marcapata', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081110', 'descripcion' => 'Ocongate', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081111', 'descripcion' => 'Oropesa', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],
            ['codigo' => '081112', 'descripcion' => 'Quiquijana', 'provincia_codigo' => '0811', 'departamento_codigo' => '08'],

            // URUBAMBA (0812)
            ['codigo' => '081201', 'descripcion' => 'Urubamba', 'provincia_codigo' => '0812', 'departamento_codigo' => '08'],
            ['codigo' => '081202', 'descripcion' => 'Chinchero', 'provincia_codigo' => '0812', 'departamento_codigo' => '08'],
            ['codigo' => '081203', 'descripcion' => 'Huayllabamba', 'provincia_codigo' => '0812', 'departamento_codigo' => '08'],
            ['codigo' => '081204', 'descripcion' => 'Machupicchu', 'provincia_codigo' => '0812', 'departamento_codigo' => '08'],
            ['codigo' => '081205', 'descripcion' => 'Maras', 'provincia_codigo' => '0812', 'departamento_codigo' => '08'],
            ['codigo' => '081206', 'descripcion' => 'Ollantaytambo', 'provincia_codigo' => '0812', 'departamento_codigo' => '08'],
            ['codigo' => '081207', 'descripcion' => 'Yucay', 'provincia_codigo' => '0812', 'departamento_codigo' => '08'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // HUANCAVELICA
            // ================================

            // PROVINCIA HUANCAVELICA (0901)
            ['codigo' => '090101', 'descripcion' => 'Huancavelica', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090102', 'descripcion' => 'Acobambilla', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090103', 'descripcion' => 'Acoria', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090104', 'descripcion' => 'Conayca', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090105', 'descripcion' => 'Cuenca', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090106', 'descripcion' => 'Huachocolpa', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090107', 'descripcion' => 'Huayllahuara', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090108', 'descripcion' => 'Izcuchaca', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090109', 'descripcion' => 'Laria', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090110', 'descripcion' => 'Manta', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090111', 'descripcion' => 'Mariscal Cáceres', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090112', 'descripcion' => 'Moya', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090113', 'descripcion' => 'Nuevo Occoro', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090114', 'descripcion' => 'Palca', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090115', 'descripcion' => 'Pilchaca', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090116', 'descripcion' => 'Vilca', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090117', 'descripcion' => 'Yauli', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090118', 'descripcion' => 'Ascensión', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],
            ['codigo' => '090119', 'descripcion' => 'Huando', 'provincia_codigo' => '0901', 'departamento_codigo' => '09'],

            // ACOBAMBA (0902)
            ['codigo' => '090201', 'descripcion' => 'Acobamba', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],
            ['codigo' => '090202', 'descripcion' => 'Andabamba', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],
            ['codigo' => '090203', 'descripcion' => 'Anta', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],
            ['codigo' => '090204', 'descripcion' => 'Caja', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],
            ['codigo' => '090205', 'descripcion' => 'Marcas', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],
            ['codigo' => '090206', 'descripcion' => 'Paucara', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],
            ['codigo' => '090207', 'descripcion' => 'Pomacocha', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],
            ['codigo' => '090208', 'descripcion' => 'Rosario', 'provincia_codigo' => '0902', 'departamento_codigo' => '09'],

            // ANGARAES (0903)
            ['codigo' => '090301', 'descripcion' => 'Lircay', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090302', 'descripcion' => 'Anchonga', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090303', 'descripcion' => 'Callanmarca', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090304', 'descripcion' => 'Ccochaccasa', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090305', 'descripcion' => 'Chincho', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090306', 'descripcion' => 'Congalla', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090307', 'descripcion' => 'Huanca-Huanca', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090308', 'descripcion' => 'Huayllay Grande', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090309', 'descripcion' => 'Julcamarca', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090310', 'descripcion' => 'San Antonio de Antaparco', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090311', 'descripcion' => 'Santo Tomás de Pata', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],
            ['codigo' => '090312', 'descripcion' => 'Secclla', 'provincia_codigo' => '0903', 'departamento_codigo' => '09'],

            // CASTROVIRREYNA (0904)
            ['codigo' => '090401', 'descripcion' => 'Castrovirreyna', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090402', 'descripcion' => 'Arma', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090403', 'descripcion' => 'Aurahuá', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090404', 'descripcion' => 'Capillas', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090405', 'descripcion' => 'Chupamarca', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090406', 'descripcion' => 'Cocas', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090407', 'descripcion' => 'Huachos', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090408', 'descripcion' => 'Huamatambo', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090409', 'descripcion' => 'Mollepampa', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090410', 'descripcion' => 'San Juan', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090411', 'descripcion' => 'Santa Ana', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090412', 'descripcion' => 'Tantara', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],
            ['codigo' => '090413', 'descripcion' => 'Ticrapo', 'provincia_codigo' => '0904', 'departamento_codigo' => '09'],

            // CHURCAMPA (0905)
            ['codigo' => '090501', 'descripcion' => 'Churcampa', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090502', 'descripcion' => 'Anco', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090503', 'descripcion' => 'Chinchihuasi', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090504', 'descripcion' => 'El Carmen', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090505', 'descripcion' => 'La Merced', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090506', 'descripcion' => 'Locroja', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090507', 'descripcion' => 'Paucarbamba', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090508', 'descripcion' => 'San Miguel de Mayocc', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090509', 'descripcion' => 'San Pedro de Coris', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],
            ['codigo' => '090510', 'descripcion' => 'Pachamarca', 'provincia_codigo' => '0905', 'departamento_codigo' => '09'],

            // HUAYTARÁ (0906)
            ['codigo' => '090601', 'descripcion' => 'Huaytará', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090602', 'descripcion' => 'Ayaví', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090603', 'descripcion' => 'Córdova', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090604', 'descripcion' => 'Huayacundo Arma', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090605', 'descripcion' => 'Laramarca', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090606', 'descripcion' => 'Ocoyo', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090607', 'descripcion' => 'Pilpichaca', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090608', 'descripcion' => 'Querco', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090609', 'descripcion' => 'Quito-Arma', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090610', 'descripcion' => 'San Antonio de Cusicancha', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090611', 'descripcion' => 'San Francisco de Sangayaico', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090612', 'descripcion' => 'San Isidro', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090613', 'descripcion' => 'Santiago de Chocorvos', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090614', 'descripcion' => 'Santiago de Quirahuará', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090615', 'descripcion' => 'Santo Domingo de Capillas', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],
            ['codigo' => '090616', 'descripcion' => 'Tambo', 'provincia_codigo' => '0906', 'departamento_codigo' => '09'],

            // TAYACAJA (0907)
            ['codigo' => '090701', 'descripcion' => 'Pampas', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090702', 'descripcion' => 'Acostambo', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090703', 'descripcion' => 'Acraquia', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090704', 'descripcion' => 'Ahuaycha', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090705', 'descripcion' => 'Colcabamba', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090706', 'descripcion' => 'Daniel Hernández', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090707', 'descripcion' => 'Huachocolpa', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090709', 'descripcion' => 'Huaribamba', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090710', 'descripcion' => 'Ñahuimpuquio', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090711', 'descripcion' => 'Pazos', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090713', 'descripcion' => 'Quishuar', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090714', 'descripcion' => 'Salcabamba', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090715', 'descripcion' => 'Salcahuasi', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090716', 'descripcion' => 'San Marcos de Rocchac', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090717', 'descripcion' => 'Surcubamba', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090718', 'descripcion' => 'Tintay Puncu', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090719', 'descripcion' => 'Quichuas', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090720', 'descripcion' => 'Andaymarca', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090721', 'descripcion' => 'Roble', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
            ['codigo' => '090722', 'descripcion' => 'Pichos', 'provincia_codigo' => '0907', 'departamento_codigo' => '09'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // HUÁNUCO (10)
            // ================================

            // PROVINCIA HUÁNUCO (1001)
            ['codigo' => '100101', 'descripcion' => 'Huánuco', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100102', 'descripcion' => 'Amarilis', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100103', 'descripcion' => 'Chinchao', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100104', 'descripcion' => 'Churubamba', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100105', 'descripcion' => 'Margos', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100106', 'descripcion' => 'Quisqui (Kichki)', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100107', 'descripcion' => 'San Francisco de Cayrán', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100108', 'descripcion' => 'San Pedro de Chaulán', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100109', 'descripcion' => 'Santa María del Valle', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100110', 'descripcion' => 'Yarumayo', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100111', 'descripcion' => 'Pillco Marca', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100112', 'descripcion' => 'Yacus', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],
            ['codigo' => '100113', 'descripcion' => 'San Pablo de Pillao', 'provincia_codigo' => '1001', 'departamento_codigo' => '10'],

            // AMBO (1002)
            ['codigo' => '100201', 'descripcion' => 'Ambo', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],
            ['codigo' => '100202', 'descripcion' => 'Cayna', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],
            ['codigo' => '100203', 'descripcion' => 'Colpas', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],
            ['codigo' => '100204', 'descripcion' => 'Conchamarca', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],
            ['codigo' => '100205', 'descripcion' => 'Huácar', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],
            ['codigo' => '100206', 'descripcion' => 'San Francisco', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],
            ['codigo' => '100207', 'descripcion' => 'San Rafael', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],
            ['codigo' => '100208', 'descripcion' => 'Tomay Kichwa', 'provincia_codigo' => '1002', 'departamento_codigo' => '10'],

            // DOS DE MAYO (1003)
            ['codigo' => '100301', 'descripcion' => 'La Unión', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100302', 'descripcion' => 'Marias', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100303', 'descripcion' => 'Pachas', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100304', 'descripcion' => 'Quivilla', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100305', 'descripcion' => 'Ripán', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100306', 'descripcion' => 'Shunqui', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100307', 'descripcion' => 'Chuquis', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100308', 'descripcion' => 'Sillapata', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],
            ['codigo' => '100309', 'descripcion' => 'Yanas', 'provincia_codigo' => '1003', 'departamento_codigo' => '10'],

            // HUACAYBAMBA (1004)
            ['codigo' => '100401', 'descripcion' => 'Huacaybamba', 'provincia_codigo' => '1004', 'departamento_codigo' => '10'],
            ['codigo' => '100402', 'descripcion' => 'Canchabamba', 'provincia_codigo' => '1004', 'departamento_codigo' => '10'],
            ['codigo' => '100403', 'descripcion' => 'Cochabamba', 'provincia_codigo' => '1004', 'departamento_codigo' => '10'],
            ['codigo' => '100404', 'descripcion' => 'Pinra', 'provincia_codigo' => '1004', 'departamento_codigo' => '10'],

            // HUAMALÍES (1005)
            ['codigo' => '100501', 'descripcion' => 'Llata', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100502', 'descripcion' => 'Arancay', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100503', 'descripcion' => 'Chavín de Pariarca', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100504', 'descripcion' => 'Jacas Grande', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100505', 'descripcion' => 'Jircán', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100506', 'descripcion' => 'Miraflores', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100507', 'descripcion' => 'Monzón', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100508', 'descripcion' => 'Punchao', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100509', 'descripcion' => 'Puños', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100510', 'descripcion' => 'Singa', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],
            ['codigo' => '100511', 'descripcion' => 'Tantamayo', 'provincia_codigo' => '1005', 'departamento_codigo' => '10'],

            // LEONCIO PRADO (1006)
            ['codigo' => '100601', 'descripcion' => 'Rupa-Rupa', 'provincia_codigo' => '1006', 'departamento_codigo' => '10'],
            ['codigo' => '100602', 'descripcion' => 'Daniel Alomía Robles', 'provincia_codigo' => '1006', 'departamento_codigo' => '10'],
            ['codigo' => '100603', 'descripcion' => 'Hermilio Valdizán', 'provincia_codigo' => '1006', 'departamento_codigo' => '10'],
            ['codigo' => '100604', 'descripcion' => 'José Crespo y Castillo', 'provincia_codigo' => '1006', 'departamento_codigo' => '10'],
            ['codigo' => '100605', 'descripcion' => 'Luyando', 'provincia_codigo' => '1006', 'departamento_codigo' => '10'],
            ['codigo' => '100606', 'descripcion' => 'Mariano Damaso Beraun', 'provincia_codigo' => '1006', 'departamento_codigo' => '10'],

            // MARAÑÓN (1007)
            ['codigo' => '100701', 'descripcion' => 'Huacrachuco', 'provincia_codigo' => '1007', 'departamento_codigo' => '10'],
            ['codigo' => '100702', 'descripcion' => 'Cholón', 'provincia_codigo' => '1007', 'departamento_codigo' => '10'],
            ['codigo' => '100703', 'descripcion' => 'San Buenaventura', 'provincia_codigo' => '1007', 'departamento_codigo' => '10'],

            // PACHITEA (1008)
            ['codigo' => '100801', 'descripcion' => 'Panao', 'provincia_codigo' => '1008', 'departamento_codigo' => '10'],
            ['codigo' => '100802', 'descripcion' => 'Chaglla', 'provincia_codigo' => '1008', 'departamento_codigo' => '10'],
            ['codigo' => '100803', 'descripcion' => 'Molino', 'provincia_codigo' => '1008', 'departamento_codigo' => '10'],
            ['codigo' => '100804', 'descripcion' => 'Umari', 'provincia_codigo' => '1008', 'departamento_codigo' => '10'],

            // PUERTO INCA (1009)
            ['codigo' => '100901', 'descripcion' => 'Puerto Inca', 'provincia_codigo' => '1009', 'departamento_codigo' => '10'],
            ['codigo' => '100902', 'descripcion' => 'Codo del Pozuzo', 'provincia_codigo' => '1009', 'departamento_codigo' => '10'],
            ['codigo' => '100903', 'descripcion' => 'Honoria', 'provincia_codigo' => '1009', 'departamento_codigo' => '10'],
            ['codigo' => '100904', 'descripcion' => 'Tournavista', 'provincia_codigo' => '1009', 'departamento_codigo' => '10'],
            ['codigo' => '100905', 'descripcion' => 'Yuyapichis', 'provincia_codigo' => '1009', 'departamento_codigo' => '10'],

            // LAURICOCHA (1010)
            ['codigo' => '101001', 'descripcion' => 'Jesús', 'provincia_codigo' => '1010', 'departamento_codigo' => '10'],
            ['codigo' => '101002', 'descripcion' => 'Baños', 'provincia_codigo' => '1010', 'departamento_codigo' => '10'],
            ['codigo' => '101003', 'descripcion' => 'Jivia', 'provincia_codigo' => '1010', 'departamento_codigo' => '10'],
            ['codigo' => '101004', 'descripcion' => 'Queropalca', 'provincia_codigo' => '1010', 'departamento_codigo' => '10'],
            ['codigo' => '101005', 'descripcion' => 'Rondos', 'provincia_codigo' => '1010', 'departamento_codigo' => '10'],
            ['codigo' => '101006', 'descripcion' => 'San Francisco de Asís', 'provincia_codigo' => '1010', 'departamento_codigo' => '10'],
            ['codigo' => '101007', 'descripcion' => 'San Miguel de Cauri', 'provincia_codigo' => '1010', 'departamento_codigo' => '10'],

            // YAROWILCA (1011)
            ['codigo' => '101101', 'descripcion' => 'Chavinillo', 'provincia_codigo' => '1011', 'departamento_codigo' => '10'],
            ['codigo' => '101102', 'descripcion' => 'Cahuac', 'provincia_codigo' => '1011', 'departamento_codigo' => '10'],
            ['codigo' => '101103', 'descripcion' => 'Chacabamba', 'provincia_codigo' => '1011', 'departamento_codigo' => '10'],
            ['codigo' => '101104', 'descripcion' => 'Aparicio Pomares', 'provincia_codigo' => '1011', 'departamento_codigo' => '10'],
            ['codigo' => '101105', 'descripcion' => 'Jacas Chico', 'provincia_codigo' => '1011', 'departamento_codigo' => '10'],
            ['codigo' => '101106', 'descripcion' => 'Obas', 'provincia_codigo' => '1011', 'departamento_codigo' => '10'],
            ['codigo' => '101107', 'descripcion' => 'Pampamarca', 'provincia_codigo' => '1011', 'departamento_codigo' => '10'],

            // ================================
            // ICA (11)
            // ================================

            // PROVINCIA ICA (1101)
            ['codigo' => '110101', 'descripcion' => 'Ica', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110102', 'descripcion' => 'La Tinguiña', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110103', 'descripcion' => 'Los Aquijes', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110104', 'descripcion' => 'Ocucaje', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110105', 'descripcion' => 'Pachacútec', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110106', 'descripcion' => 'Parcona', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110107', 'descripcion' => 'Pueblo Nuevo', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110108', 'descripcion' => 'Salas', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110109', 'descripcion' => 'San José de los Molinos', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110110', 'descripcion' => 'San Juan Bautista', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110111', 'descripcion' => 'Santiago', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110112', 'descripcion' => 'Subtanjalla', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110113', 'descripcion' => 'Tate', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],
            ['codigo' => '110114', 'descripcion' => 'Yauca del Rosario', 'provincia_codigo' => '1101', 'departamento_codigo' => '11'],

            // CHINCHA (1102)
            ['codigo' => '110201', 'descripcion' => 'Chincha Alta', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110202', 'descripcion' => 'Alto Larán', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110203', 'descripcion' => 'Chavín', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110204', 'descripcion' => 'Chincha Baja', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110205', 'descripcion' => 'El Carmen', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110206', 'descripcion' => 'Grocio Prado', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110207', 'descripcion' => 'Pueblo Nuevo', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110208', 'descripcion' => 'San Juan de Yanac', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110209', 'descripcion' => 'San Pedro de Huacarpana', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110210', 'descripcion' => 'Sunampe', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],
            ['codigo' => '110211', 'descripcion' => 'Tambo de Mora', 'provincia_codigo' => '1102', 'departamento_codigo' => '11'],

            // NAZCA (1103)
            ['codigo' => '110301', 'descripcion' => 'Nazca', 'provincia_codigo' => '1103', 'departamento_codigo' => '11'],
            ['codigo' => '110302', 'descripcion' => 'Changuillo', 'provincia_codigo' => '1103', 'departamento_codigo' => '11'],
            ['codigo' => '110303', 'descripcion' => 'El Ingenio', 'provincia_codigo' => '1103', 'departamento_codigo' => '11'],
            ['codigo' => '110304', 'descripcion' => 'Marcona', 'provincia_codigo' => '1103', 'departamento_codigo' => '11'],
            ['codigo' => '110305', 'descripcion' => 'Vista Alegre', 'provincia_codigo' => '1103', 'departamento_codigo' => '11'],

            // PALPA (1104)
            ['codigo' => '110401', 'descripcion' => 'Palpa', 'provincia_codigo' => '1104', 'departamento_codigo' => '11'],
            ['codigo' => '110402', 'descripcion' => 'Llipata', 'provincia_codigo' => '1104', 'departamento_codigo' => '11'],
            ['codigo' => '110403', 'descripcion' => 'Río Grande', 'provincia_codigo' => '1104', 'departamento_codigo' => '11'],
            ['codigo' => '110404', 'descripcion' => 'Santa Cruz', 'provincia_codigo' => '1104', 'departamento_codigo' => '11'],
            ['codigo' => '110405', 'descripcion' => 'Tibillo', 'provincia_codigo' => '1104', 'departamento_codigo' => '11'],

            // PISCO (1105)
            ['codigo' => '110501', 'descripcion' => 'Pisco', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
            ['codigo' => '110502', 'descripcion' => 'Huancano', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
            ['codigo' => '110503', 'descripcion' => 'Humay', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
            ['codigo' => '110504', 'descripcion' => 'Independencia', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
            ['codigo' => '110505', 'descripcion' => 'Paracas', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
            ['codigo' => '110506', 'descripcion' => 'San Andrés', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
            ['codigo' => '110507', 'descripcion' => 'San Clemente', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
            ['codigo' => '110508', 'descripcion' => 'Túpac Amaru Inca', 'provincia_codigo' => '1105', 'departamento_codigo' => '11'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // JUNÍN (12)
            // ================================

            // PROVINCIA HUANCAYO (1201)
            ['codigo' => '120101', 'descripcion' => 'Huancayo', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120104', 'descripcion' => 'Carhuacallanga', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120105', 'descripcion' => 'Chacapampa', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120106', 'descripcion' => 'Chicche', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120107', 'descripcion' => 'Chilca', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120108', 'descripcion' => 'Chongos Alto', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120111', 'descripcion' => 'Chupuro', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120112', 'descripcion' => 'Colca', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120113', 'descripcion' => 'Cullhuas', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120114', 'descripcion' => 'El Tambo', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120116', 'descripcion' => 'Huacrapuquio', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120117', 'descripcion' => 'Hualhuas', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120119', 'descripcion' => 'Huancán', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120120', 'descripcion' => 'Huasicancha', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120121', 'descripcion' => 'Huayucachi', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120122', 'descripcion' => 'Ingenio', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120124', 'descripcion' => 'Pariahuanca', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120125', 'descripcion' => 'Pilcomayo', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120126', 'descripcion' => 'Pucara', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120127', 'descripcion' => 'Quichuay', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120128', 'descripcion' => 'Quilcas', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120129', 'descripcion' => 'San Agustín', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120130', 'descripcion' => 'San Jerónimo de Tunán', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120132', 'descripcion' => 'Saño', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120133', 'descripcion' => 'Sapallanga', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120134', 'descripcion' => 'Sicaya', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120135', 'descripcion' => 'Santo Domingo de Acobamba', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],
            ['codigo' => '120136', 'descripcion' => 'Viques', 'provincia_codigo' => '1201', 'departamento_codigo' => '12'],

            // CONCEPCIÓN (1202)
            ['codigo' => '120201', 'descripcion' => 'Concepción', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120202', 'descripcion' => 'Aco', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120203', 'descripcion' => 'Andamarca', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120204', 'descripcion' => 'Chambara', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120205', 'descripcion' => 'Cochas', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120206', 'descripcion' => 'Comas', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120207', 'descripcion' => 'Heroinas Toledo', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120208', 'descripcion' => 'Manzanares', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120209', 'descripcion' => 'Mariscal Castilla', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120210', 'descripcion' => 'Matahuasi', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120211', 'descripcion' => 'Mito', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120212', 'descripcion' => 'Nueve de Julio', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120213', 'descripcion' => 'Orcotuna', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120214', 'descripcion' => 'San José de Quero', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],
            ['codigo' => '120215', 'descripcion' => 'Santa Rosa de Ocopa', 'provincia_codigo' => '1202', 'departamento_codigo' => '12'],

            // CHANCHAMAYO (1203)
            ['codigo' => '120301', 'descripcion' => 'Chanchamayo', 'provincia_codigo' => '1203', 'departamento_codigo' => '12'],
            ['codigo' => '120302', 'descripcion' => 'Perené', 'provincia_codigo' => '1203', 'departamento_codigo' => '12'],
            ['codigo' => '120303', 'descripcion' => 'Pichanaqui', 'provincia_codigo' => '1203', 'departamento_codigo' => '12'],
            ['codigo' => '120304', 'descripcion' => 'San Luis de Shuaro', 'provincia_codigo' => '1203', 'departamento_codigo' => '12'],
            ['codigo' => '120305', 'descripcion' => 'San Ramón', 'provincia_codigo' => '1203', 'departamento_codigo' => '12'],
            ['codigo' => '120306', 'descripcion' => 'Vitoc', 'provincia_codigo' => '1203', 'departamento_codigo' => '12'],

            // JAUJA (1204)
            ['codigo' => '120401', 'descripcion' => 'Jauja', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120402', 'descripcion' => 'Acolla', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120403', 'descripcion' => 'Apata', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120404', 'descripcion' => 'Ataura', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120405', 'descripcion' => 'Canchayllo', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120406', 'descripcion' => 'Curicaca', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120407', 'descripcion' => 'El Mantaro', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120408', 'descripcion' => 'Huamalí', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120409', 'descripcion' => 'Huaripampa', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120410', 'descripcion' => 'Huertas', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120411', 'descripcion' => 'Janjaillo', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120412', 'descripcion' => 'Julcán', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120413', 'descripcion' => 'Leonor Ordóñez', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120414', 'descripcion' => 'Llocllapampa', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120415', 'descripcion' => 'Marco', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120416', 'descripcion' => 'Masma', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120417', 'descripcion' => 'Masma Chicche', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120418', 'descripcion' => 'Molinos', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120419', 'descripcion' => 'Monobamba', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120420', 'descripcion' => 'Muqui', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120421', 'descripcion' => 'Muquiyauyo', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120422', 'descripcion' => 'Paca', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120423', 'descripcion' => 'Paccha', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120424', 'descripcion' => 'Pancán', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120425', 'descripcion' => 'Parco', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120426', 'descripcion' => 'Pomacancha', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120427', 'descripcion' => 'Ricrán', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120428', 'descripcion' => 'San Lorenzo', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120429', 'descripcion' => 'San Pedro de Chunán', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120430', 'descripcion' => 'Sausa', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120431', 'descripcion' => 'Sincos', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120432', 'descripcion' => 'Tunan Marca', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120433', 'descripcion' => 'Yauli', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
            ['codigo' => '120434', 'descripcion' => 'Yauyos', 'provincia_codigo' => '1204', 'departamento_codigo' => '12'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // LA LIBERTAD (13)
            // ================================

            // TRUJILLO (1301)
            ['codigo' => '130101', 'descripcion' => 'Trujillo', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130102', 'descripcion' => 'El Porvenir', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130103', 'descripcion' => 'Florencia de Mora', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130104', 'descripcion' => 'Huanchaco', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130105', 'descripcion' => 'La Esperanza', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130106', 'descripcion' => 'Laredo', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130107', 'descripcion' => 'Moche', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130108', 'descripcion' => 'Poroto', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130109', 'descripcion' => 'Salaverry', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130110', 'descripcion' => 'Simbal', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],
            ['codigo' => '130111', 'descripcion' => 'Victor Larco Herrera', 'provincia_codigo' => '1301', 'departamento_codigo' => '13'],

            // ASCOPE (1302)
            ['codigo' => '130201', 'descripcion' => 'Ascope', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],
            ['codigo' => '130202', 'descripcion' => 'Chicama', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],
            ['codigo' => '130203', 'descripcion' => 'Chocope', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],
            ['codigo' => '130204', 'descripcion' => 'Magdalena de Cao', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],
            ['codigo' => '130205', 'descripcion' => 'Paiján', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],
            ['codigo' => '130206', 'descripcion' => 'Rázuri', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],
            ['codigo' => '130207', 'descripcion' => 'Santiago de Cao', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],
            ['codigo' => '130208', 'descripcion' => 'Casa Grande', 'provincia_codigo' => '1302', 'departamento_codigo' => '13'],

            // BOLÍVAR (1303)
            ['codigo' => '130301', 'descripcion' => 'Bolívar', 'provincia_codigo' => '1303', 'departamento_codigo' => '13'],
            ['codigo' => '130302', 'descripcion' => 'Bambamarca', 'provincia_codigo' => '1303', 'departamento_codigo' => '13'],
            ['codigo' => '130303', 'descripcion' => 'Condormarca', 'provincia_codigo' => '1303', 'departamento_codigo' => '13'],
            ['codigo' => '130304', 'descripcion' => 'Longotea', 'provincia_codigo' => '1303', 'departamento_codigo' => '13'],
            ['codigo' => '130305', 'descripcion' => 'Uchumarca', 'provincia_codigo' => '1303', 'departamento_codigo' => '13'],
            ['codigo' => '130306', 'descripcion' => 'Ucuncha', 'provincia_codigo' => '1303', 'departamento_codigo' => '13'],

            // CHEPÉN (1304)
            ['codigo' => '130401', 'descripcion' => 'Chepén', 'provincia_codigo' => '1304', 'departamento_codigo' => '13'],
            ['codigo' => '130402', 'descripcion' => 'Pacanga', 'provincia_codigo' => '1304', 'departamento_codigo' => '13'],
            ['codigo' => '130403', 'descripcion' => 'Pueblo Nuevo', 'provincia_codigo' => '1304', 'departamento_codigo' => '13'],

            // JULCÁN (1305)
            ['codigo' => '130501', 'descripcion' => 'Julcán', 'provincia_codigo' => '1305', 'departamento_codigo' => '13'],
            ['codigo' => '130502', 'descripcion' => 'Calamarca', 'provincia_codigo' => '1305', 'departamento_codigo' => '13'],
            ['codigo' => '130503', 'descripcion' => 'Carabamba', 'provincia_codigo' => '1305', 'departamento_codigo' => '13'],
            ['codigo' => '130504', 'descripcion' => 'Huaso', 'provincia_codigo' => '1305', 'departamento_codigo' => '13'],

            // OTUZCO (1306)
            ['codigo' => '130601', 'descripcion' => 'Otuzco', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130602', 'descripcion' => 'Agallpampa', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130604', 'descripcion' => 'Charat', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130605', 'descripcion' => 'Huarantchal', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130606', 'descripcion' => 'La Cuesta', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130608', 'descripcion' => 'Mache', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130610', 'descripcion' => 'Paranday', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130611', 'descripcion' => 'Salpo', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130613', 'descripcion' => 'Sinsicap', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],
            ['codigo' => '130614', 'descripcion' => 'Usquil', 'provincia_codigo' => '1306', 'departamento_codigo' => '13'],

            // PACASMAYO (1307)
            ['codigo' => '130701', 'descripcion' => 'San Pedro de Lloc', 'provincia_codigo' => '1307', 'departamento_codigo' => '13'],
            ['codigo' => '130702', 'descripcion' => 'Guadalupe', 'provincia_codigo' => '1307', 'departamento_codigo' => '13'],
            ['codigo' => '130703', 'descripcion' => 'Jequetepeque', 'provincia_codigo' => '1307', 'departamento_codigo' => '13'],
            ['codigo' => '130704', 'descripcion' => 'Pacasmayo', 'provincia_codigo' => '1307', 'departamento_codigo' => '13'],
            ['codigo' => '130705', 'descripcion' => 'San José', 'provincia_codigo' => '1307', 'departamento_codigo' => '13'],

            // PATAZ (1308)
            ['codigo' => '130801', 'descripcion' => 'Tayabamba', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130802', 'descripcion' => 'Buldibuyo', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130803', 'descripcion' => 'Chillia', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130804', 'descripcion' => 'Huancaspata', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130805', 'descripcion' => 'Huaylillas', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130806', 'descripcion' => 'Huayo', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130807', 'descripcion' => 'Ongón', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130808', 'descripcion' => 'Parcoy', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130809', 'descripcion' => 'Pataz', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130810', 'descripcion' => 'Pias', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130811', 'descripcion' => 'Santiago de Challas', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130812', 'descripcion' => 'Taurija', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],
            ['codigo' => '130813', 'descripcion' => 'Urpay', 'provincia_codigo' => '1308', 'departamento_codigo' => '13'],

            // SÁNCHEZ CARRIÓN (1309)
            ['codigo' => '130901', 'descripcion' => 'Huamachuco', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],
            ['codigo' => '130902', 'descripcion' => 'Chugay', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],
            ['codigo' => '130903', 'descripcion' => 'Cochorco', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],
            ['codigo' => '130904', 'descripcion' => 'Curgos', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],
            ['codigo' => '130905', 'descripcion' => 'Marcabal', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],
            ['codigo' => '130906', 'descripcion' => 'Sanagorán', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],
            ['codigo' => '130907', 'descripcion' => 'Sarín', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],
            ['codigo' => '130908', 'descripcion' => 'Sartimbamba', 'provincia_codigo' => '1309', 'departamento_codigo' => '13'],

            // SANTIAGO DE CHUCO (1310)
            ['codigo' => '131001', 'descripcion' => 'Santiago de Chuco', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],
            ['codigo' => '131002', 'descripcion' => 'Angasmarca', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],
            ['codigo' => '131003', 'descripcion' => 'Cachicadán', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],
            ['codigo' => '131004', 'descripcion' => 'Mollebamba', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],
            ['codigo' => '131005', 'descripcion' => 'Mollepata', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],
            ['codigo' => '131006', 'descripcion' => 'Quiruvilca', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],
            ['codigo' => '131007', 'descripcion' => 'Santa Cruz de Chuca', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],
            ['codigo' => '131008', 'descripcion' => 'Sitabamba', 'provincia_codigo' => '1310', 'departamento_codigo' => '13'],

            // GRAN CHIMÚ (1311)
            ['codigo' => '131101', 'descripcion' => 'Cascas', 'provincia_codigo' => '1311', 'departamento_codigo' => '13'],
            ['codigo' => '131102', 'descripcion' => 'Lucma', 'provincia_codigo' => '1311', 'departamento_codigo' => '13'],
            ['codigo' => '131103', 'descripcion' => 'Marmot', 'provincia_codigo' => '1311', 'departamento_codigo' => '13'],
            ['codigo' => '131104', 'descripcion' => 'Sayapullo', 'provincia_codigo' => '1311', 'departamento_codigo' => '13'],

            // VIRÚ (1312)
            ['codigo' => '131201', 'descripcion' => 'Virú', 'provincia_codigo' => '1312', 'departamento_codigo' => '13'],
            ['codigo' => '131202', 'descripcion' => 'Chao', 'provincia_codigo' => '1312', 'departamento_codigo' => '13'],
            ['codigo' => '131203', 'descripcion' => 'Guadalupito', 'provincia_codigo' => '1312', 'departamento_codigo' => '13'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // LAMBAYEQUE (14)
            // ================================

            // CHICLAYO (1401)
            ['codigo' => '140101', 'descripcion' => 'Chiclayo', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140102', 'descripcion' => 'Chongoyape', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140103', 'descripcion' => 'Eten', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140104', 'descripcion' => 'Eten Puerto', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140105', 'descripcion' => 'José Leonardo Ortiz', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140106', 'descripcion' => 'La Victoria', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140107', 'descripcion' => 'Lagunas', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140108', 'descripcion' => 'Monsefú', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140109', 'descripcion' => 'Nueva Arica', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140110', 'descripcion' => 'Oyotún', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140111', 'descripcion' => 'Picsi', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140112', 'descripcion' => 'Pimentel', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140113', 'descripcion' => 'Reque', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140114', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140115', 'descripcion' => 'Saña', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140116', 'descripcion' => 'Cayaltí', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140117', 'descripcion' => 'Patapo', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140118', 'descripcion' => 'Pomalca', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140119', 'descripcion' => 'Pucala', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],
            ['codigo' => '140120', 'descripcion' => 'Tumán', 'provincia_codigo' => '1401', 'departamento_codigo' => '14'],

            // FERREÑAFE (1402)
            ['codigo' => '140201', 'descripcion' => 'Ferreñafe', 'provincia_codigo' => '1402', 'departamento_codigo' => '14'],
            ['codigo' => '140202', 'descripcion' => 'Cañaris', 'provincia_codigo' => '1402', 'departamento_codigo' => '14'],
            ['codigo' => '140203', 'descripcion' => 'Incahuasi', 'provincia_codigo' => '1402', 'departamento_codigo' => '14'],
            ['codigo' => '140204', 'descripcion' => 'Manuel Antonio Mesones Muro', 'provincia_codigo' => '1402', 'departamento_codigo' => '14'],
            ['codigo' => '140205', 'descripcion' => 'Pítipo', 'provincia_codigo' => '1402', 'departamento_codigo' => '14'],
            ['codigo' => '140206', 'descripcion' => 'Pueblo Nuevo', 'provincia_codigo' => '1402', 'departamento_codigo' => '14'],

            // LAMBAYEQUE (1403)
            ['codigo' => '140301', 'descripcion' => 'Lambayeque', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140302', 'descripcion' => 'Chochope', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140303', 'descripcion' => 'Íllimo', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140304', 'descripcion' => 'Jayanca', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140305', 'descripcion' => 'Mochumí', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140306', 'descripcion' => 'Mórrope', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140307', 'descripcion' => 'Motupe', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140308', 'descripcion' => 'Olmos', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140309', 'descripcion' => 'Pacora', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140310', 'descripcion' => 'Salas', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140311', 'descripcion' => 'San José', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
            ['codigo' => '140312', 'descripcion' => 'Túcume', 'provincia_codigo' => '1403', 'departamento_codigo' => '14'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // LIMA (15)
            // ================================

            // PROVINCIA LIMA (1501)
            ['codigo' => '150101', 'descripcion' => 'Lima', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150102', 'descripcion' => 'Ancón', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150103', 'descripcion' => 'Ate', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150104', 'descripcion' => 'Barranco', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150105', 'descripcion' => 'Breña', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150106', 'descripcion' => 'Carabayllo', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150107', 'descripcion' => 'Chaclacayo', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150108', 'descripcion' => 'Chorrillos', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150109', 'descripcion' => 'Cieneguilla', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150110', 'descripcion' => 'Comas', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150111', 'descripcion' => 'El Agustino', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150112', 'descripcion' => 'Independencia', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150113', 'descripcion' => 'Jesús María', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150114', 'descripcion' => 'La Molina', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150115', 'descripcion' => 'La Victoria', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150116', 'descripcion' => 'Lince', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150117', 'descripcion' => 'Los Olivos', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150118', 'descripcion' => 'Lurigancho', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150119', 'descripcion' => 'Lurín', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150120', 'descripcion' => 'Magdalena del Mar', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150121', 'descripcion' => 'Pueblo Libre', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150122', 'descripcion' => 'Miraflores', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150123', 'descripcion' => 'Pachacámac', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150124', 'descripcion' => 'Pucusana', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150125', 'descripcion' => 'Puente Piedra', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150126', 'descripcion' => 'Punta Hermosa', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150127', 'descripcion' => 'Punta Negra', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150128', 'descripcion' => 'Rímac', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150129', 'descripcion' => 'San Bartolo', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150130', 'descripcion' => 'San Borja', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150131', 'descripcion' => 'San Isidro', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150132', 'descripcion' => 'San Juan de Lurigancho', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150133', 'descripcion' => 'San Juan de Miraflores', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150134', 'descripcion' => 'San Luis', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150135', 'descripcion' => 'San Martín de Porres', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150136', 'descripcion' => 'San Miguel', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150137', 'descripcion' => 'Santa Anita', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150138', 'descripcion' => 'Santa María del Mar', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150139', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150140', 'descripcion' => 'Santiago de Surco', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150141', 'descripcion' => 'Surquillo', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150142', 'descripcion' => 'Villa El Salvador', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],
            ['codigo' => '150143', 'descripcion' => 'Villa María del Triunfo', 'provincia_codigo' => '1501', 'departamento_codigo' => '15'],

            // BARRANCA (1502)
            ['codigo' => '150201', 'descripcion' => 'Barranca', 'provincia_codigo' => '1502', 'departamento_codigo' => '15'],
            ['codigo' => '150202', 'descripcion' => 'Paramonga', 'provincia_codigo' => '1502', 'departamento_codigo' => '15'],
            ['codigo' => '150203', 'descripcion' => 'Pativilca', 'provincia_codigo' => '1502', 'departamento_codigo' => '15'],
            ['codigo' => '150204', 'descripcion' => 'Supe', 'provincia_codigo' => '1502', 'departamento_codigo' => '15'],
            ['codigo' => '150205', 'descripcion' => 'Supe Puerto', 'provincia_codigo' => '1502', 'departamento_codigo' => '15'],

            // CAJATAMBO (1503)
            ['codigo' => '150301', 'descripcion' => 'Cajatambo', 'provincia_codigo' => '1503', 'departamento_codigo' => '15'],
            ['codigo' => '150302', 'descripcion' => 'Copa', 'provincia_codigo' => '1503', 'departamento_codigo' => '15'],
            ['codigo' => '150303', 'descripcion' => 'Gorgor', 'provincia_codigo' => '1503', 'departamento_codigo' => '15'],
            ['codigo' => '150304', 'descripcion' => 'Huancapón', 'provincia_codigo' => '1503', 'departamento_codigo' => '15'],
            ['codigo' => '150305', 'descripcion' => 'Manás', 'provincia_codigo' => '1503', 'departamento_codigo' => '15'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // LIMA (15) - continuación
            // ================================

            // CANTA (1504)
            ['codigo' => '150401', 'descripcion' => 'Canta', 'provincia_codigo' => '1504', 'departamento_codigo' => '15'],
            ['codigo' => '150402', 'descripcion' => 'Arahuay', 'provincia_codigo' => '1504', 'departamento_codigo' => '15'],
            ['codigo' => '150403', 'descripcion' => 'Huamantanga', 'provincia_codigo' => '1504', 'departamento_codigo' => '15'],
            ['codigo' => '150404', 'descripcion' => 'Huaros', 'provincia_codigo' => '1504', 'departamento_codigo' => '15'],
            ['codigo' => '150405', 'descripcion' => 'Lachaqui', 'provincia_codigo' => '1504', 'departamento_codigo' => '15'],
            ['codigo' => '150406', 'descripcion' => 'San Buenaventura', 'provincia_codigo' => '1504', 'departamento_codigo' => '15'],
            ['codigo' => '150407', 'descripcion' => 'Santa Rosa de Quives', 'provincia_codigo' => '1504', 'departamento_codigo' => '15'],

            // CAÑETE (1505)
            ['codigo' => '150501', 'descripcion' => 'San Vicente de Cañete', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150502', 'descripcion' => 'Asia', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150503', 'descripcion' => 'Calango', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150504', 'descripcion' => 'Cerro Azul', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150505', 'descripcion' => 'Chilca', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150506', 'descripcion' => 'Coayllo', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150507', 'descripcion' => 'Imperial', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150508', 'descripcion' => 'Lunahuaná', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150509', 'descripcion' => 'Mala', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150510', 'descripcion' => 'Nuevo Imperial', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150511', 'descripcion' => 'Pacarán', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150512', 'descripcion' => 'Quilmaná', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150513', 'descripcion' => 'San Antonio', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150514', 'descripcion' => 'San Luis', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150515', 'descripcion' => 'Santa Cruz de Flores', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],
            ['codigo' => '150516', 'descripcion' => 'Zúñiga', 'provincia_codigo' => '1505', 'departamento_codigo' => '15'],

            // HUARAL (1506)
            ['codigo' => '150601', 'descripcion' => 'Huaral', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150602', 'descripcion' => 'Atavillos Alto', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150603', 'descripcion' => 'Atavillos Bajo', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150604', 'descripcion' => 'Aucallama', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150605', 'descripcion' => 'Chancay', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150606', 'descripcion' => 'Ihuarí', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150607', 'descripcion' => 'Lampián', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150608', 'descripcion' => 'Pacaraos', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150609', 'descripcion' => 'San Miguel de Acos', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150610', 'descripcion' => 'Santa Cruz de Andamarca', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150611', 'descripcion' => 'Sumbilca', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],
            ['codigo' => '150612', 'descripcion' => 'Veintisiete de Noviembre', 'provincia_codigo' => '1506', 'departamento_codigo' => '15'],

            // HUAROCHIRÍ (1507)
            ['codigo' => '150701', 'descripcion' => 'Matucana', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150702', 'descripcion' => 'Antioquía', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150703', 'descripcion' => 'Callahuanca', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150704', 'descripcion' => 'Carampoma', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150705', 'descripcion' => 'Chicla', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150706', 'descripcion' => 'Cuenca', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150707', 'descripcion' => 'Huachupampa', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150708', 'descripcion' => 'Huanza', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150709', 'descripcion' => 'Huarochirí', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150710', 'descripcion' => 'Lahuaytambo', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150711', 'descripcion' => 'Langa', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150712', 'descripcion' => 'Laraos', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150713', 'descripcion' => 'Mariatana', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150714', 'descripcion' => 'Ricardo Palma', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150715', 'descripcion' => 'San Andrés de Tupicocha', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150716', 'descripcion' => 'San Antonio', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150717', 'descripcion' => 'San Bartolomé', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150718', 'descripcion' => 'San Damián', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150719', 'descripcion' => 'San Juan de Iris', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150720', 'descripcion' => 'San Juan de Tantaranche', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150721', 'descripcion' => 'San Lorenzo de Quinti', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150722', 'descripcion' => 'San Mateo', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150723', 'descripcion' => 'San Mateo de Otao', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150724', 'descripcion' => 'San Pedro de Casta', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150725', 'descripcion' => 'San Pedro de Huancayre', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150726', 'descripcion' => 'Sangallaya', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150727', 'descripcion' => 'Santa Cruz de Cocachacra', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150728', 'descripcion' => 'Santa Eulalia', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150729', 'descripcion' => 'Santiago de Anchucaya', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150730', 'descripcion' => 'Santiago de Tuna', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150731', 'descripcion' => 'Santo Domingo de los Olleros', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],
            ['codigo' => '150732', 'descripcion' => 'Surco', 'provincia_codigo' => '1507', 'departamento_codigo' => '15'],

            // HUAURA (1508)
            ['codigo' => '150801', 'descripcion' => 'Huacho', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150802', 'descripcion' => 'Ambar', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150803', 'descripcion' => 'Caleta de Carquín', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150804', 'descripcion' => 'Checras', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150805', 'descripcion' => 'Hualmay', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150806', 'descripcion' => 'Huaura', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150807', 'descripcion' => 'Leoncio Prado', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150808', 'descripcion' => 'Paccho', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150809', 'descripcion' => 'Santa Leonor', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150810', 'descripcion' => 'Santa María', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150811', 'descripcion' => 'Sayán', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],
            ['codigo' => '150812', 'descripcion' => 'Végueta', 'provincia_codigo' => '1508', 'departamento_codigo' => '15'],

            // OYÓN (1509)
            ['codigo' => '150901', 'descripcion' => 'Oyón', 'provincia_codigo' => '1509', 'departamento_codigo' => '15'],
            ['codigo' => '150902', 'descripcion' => 'Andajes', 'provincia_codigo' => '1509', 'departamento_codigo' => '15'],
            ['codigo' => '150903', 'descripcion' => 'Caujul', 'provincia_codigo' => '1509', 'departamento_codigo' => '15'],
            ['codigo' => '150904', 'descripcion' => 'Cochamarca', 'provincia_codigo' => '1509', 'departamento_codigo' => '15'],
            ['codigo' => '150905', 'descripcion' => 'Naván', 'provincia_codigo' => '1509', 'departamento_codigo' => '15'],
            ['codigo' => '150906', 'descripcion' => 'Pachangara', 'provincia_codigo' => '1509', 'departamento_codigo' => '15'],

            // YAUYOS (1510)
            ['codigo' => '151001', 'descripcion' => 'Yauyos', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151002', 'descripcion' => 'Alis', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151003', 'descripcion' => 'Allauca', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151004', 'descripcion' => 'Ayavirí', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151005', 'descripcion' => 'Azángaro', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151006', 'descripcion' => 'Cacra', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151007', 'descripcion' => 'Carania', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151008', 'descripcion' => 'Catahuasi', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151009', 'descripcion' => 'Chocos', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151010', 'descripcion' => 'Cochas', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151011', 'descripcion' => 'Colonia', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151012', 'descripcion' => 'Hongos', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151013', 'descripcion' => 'Huampará', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151014', 'descripcion' => 'Huancaya', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151015', 'descripcion' => 'Huangáscar', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151016', 'descripcion' => 'Huantán', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151017', 'descripcion' => 'Huañec', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151018', 'descripcion' => 'Laraos', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151019', 'descripcion' => 'Lincha', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151020', 'descripcion' => 'Madean', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151021', 'descripcion' => 'Miraflores', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151022', 'descripcion' => 'Omas', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151023', 'descripcion' => 'Putinza', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151024', 'descripcion' => 'Quinches', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151025', 'descripcion' => 'Quinocay', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151026', 'descripcion' => 'San Joaquín', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151027', 'descripcion' => 'San Pedro de Pilas', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151028', 'descripcion' => 'Tanta', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151029', 'descripcion' => 'Tauripampa', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151030', 'descripcion' => 'Tomás', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151031', 'descripcion' => 'Tupe', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151032', 'descripcion' => 'Viñac', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
            ['codigo' => '151033', 'descripcion' => 'Vitis', 'provincia_codigo' => '1510', 'departamento_codigo' => '15'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // LORETO (16)
            // ================================

            // MAYNAS (1601)
            ['codigo' => '160101', 'descripcion' => 'Iquitos', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160102', 'descripcion' => 'Alto Nanay', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160103', 'descripcion' => 'Fernando Lores', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160104', 'descripcion' => 'Indiana', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160105', 'descripcion' => 'Las Amazonas', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160106', 'descripcion' => 'Mazán', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160107', 'descripcion' => 'Napo', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160108', 'descripcion' => 'Punchana', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160109', 'descripcion' => 'Putumayo', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160110', 'descripcion' => 'Torres Causana', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160111', 'descripcion' => 'Belén', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],
            ['codigo' => '160112', 'descripcion' => 'San Juan Bautista', 'provincia_codigo' => '1601', 'departamento_codigo' => '16'],

            // ALTO AMAZONAS (1602)
            ['codigo' => '160201', 'descripcion' => 'Yurimaguas', 'provincia_codigo' => '1602', 'departamento_codigo' => '16'],
            ['codigo' => '160202', 'descripcion' => 'Balsapuerto', 'provincia_codigo' => '1602', 'departamento_codigo' => '16'],
            ['codigo' => '160205', 'descripcion' => 'Jeberos', 'provincia_codigo' => '1602', 'departamento_codigo' => '16'],
            ['codigo' => '160206', 'descripcion' => 'Lagunas', 'provincia_codigo' => '1602', 'departamento_codigo' => '16'],
            ['codigo' => '160210', 'descripcion' => 'Santa Cruz', 'provincia_codigo' => '1602', 'departamento_codigo' => '16'],
            ['codigo' => '160211', 'descripcion' => 'Teniente César López Rojas', 'provincia_codigo' => '1602', 'departamento_codigo' => '16'],

            // LORETO (1603)
            ['codigo' => '160301', 'descripcion' => 'Nauta', 'provincia_codigo' => '1603', 'departamento_codigo' => '16'],
            ['codigo' => '160302', 'descripcion' => 'Parinari', 'provincia_codigo' => '1603', 'departamento_codigo' => '16'],
            ['codigo' => '160303', 'descripcion' => 'Tigre', 'provincia_codigo' => '1603', 'departamento_codigo' => '16'],
            ['codigo' => '160304', 'descripcion' => 'Trompeteros', 'provincia_codigo' => '1603', 'departamento_codigo' => '16'],
            ['codigo' => '160305', 'descripcion' => 'Urarinas', 'provincia_codigo' => '1603', 'departamento_codigo' => '16'],

            // MARISCAL RAMÓN CASTILLA (1604)
            ['codigo' => '160401', 'descripcion' => 'Ramón Castilla', 'provincia_codigo' => '1604', 'departamento_codigo' => '16'],
            ['codigo' => '160402', 'descripcion' => 'Pebas', 'provincia_codigo' => '1604', 'departamento_codigo' => '16'],
            ['codigo' => '160403', 'descripcion' => 'Yavarí', 'provincia_codigo' => '1604', 'departamento_codigo' => '16'],
            ['codigo' => '160404', 'descripcion' => 'San Pablo', 'provincia_codigo' => '1604', 'departamento_codigo' => '16'],

            // REQUENA (1605)
            ['codigo' => '160501', 'descripcion' => 'Requena', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160502', 'descripcion' => 'Alto Tapiche', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160503', 'descripcion' => 'Capelo', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160504', 'descripcion' => 'Emilio San Martín', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160505', 'descripcion' => 'Maquía', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160506', 'descripcion' => 'Puinahua', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160507', 'descripcion' => 'Saquena', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160508', 'descripcion' => 'Soplin', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160509', 'descripcion' => 'Tapiche', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160510', 'descripcion' => 'Jenaro Herrera', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],
            ['codigo' => '160511', 'descripcion' => 'Yaquerana', 'provincia_codigo' => '1605', 'departamento_codigo' => '16'],

            // UCAYALI (1606)
            ['codigo' => '160601', 'descripcion' => 'Contamana', 'provincia_codigo' => '1606', 'departamento_codigo' => '16'],
            ['codigo' => '160602', 'descripcion' => 'Inahuaya', 'provincia_codigo' => '1606', 'departamento_codigo' => '16'],
            ['codigo' => '160603', 'descripcion' => 'Padre Márquez', 'provincia_codigo' => '1606', 'departamento_codigo' => '16'],
            ['codigo' => '160604', 'descripcion' => 'Pampa Hermosa', 'provincia_codigo' => '1606', 'departamento_codigo' => '16'],
            ['codigo' => '160605', 'descripcion' => 'Sarayacu', 'provincia_codigo' => '1606', 'departamento_codigo' => '16'],
            ['codigo' => '160606', 'descripcion' => 'Vargas Guerra', 'provincia_codigo' => '1606', 'departamento_codigo' => '16'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // MADRE DE DIOS (17)
            // ================================

            // TAMBOPATA (1701)
            ['codigo' => '170101', 'descripcion' => 'Tambopata', 'provincia_codigo' => '1701', 'departamento_codigo' => '17'],
            ['codigo' => '170102', 'descripcion' => 'Inambari', 'provincia_codigo' => '1701', 'departamento_codigo' => '17'],
            ['codigo' => '170103', 'descripcion' => 'Las Piedras', 'provincia_codigo' => '1701', 'departamento_codigo' => '17'],
            ['codigo' => '170104', 'descripcion' => 'Laberinto', 'provincia_codigo' => '1701', 'departamento_codigo' => '17'],

            // MANU (1702)
            ['codigo' => '170201', 'descripcion' => 'Manu', 'provincia_codigo' => '1702', 'departamento_codigo' => '17'],
            ['codigo' => '170202', 'descripcion' => 'Fitzcarrald', 'provincia_codigo' => '1702', 'departamento_codigo' => '17'],
            ['codigo' => '170203', 'descripcion' => 'Madre de Dios', 'provincia_codigo' => '1702', 'departamento_codigo' => '17'],
            ['codigo' => '170204', 'descripcion' => 'Huepetuhe', 'provincia_codigo' => '1702', 'departamento_codigo' => '17'],

            // TAHUAMANU (1703)
            ['codigo' => '170301', 'descripcion' => 'Iñapari', 'provincia_codigo' => '1703', 'departamento_codigo' => '17'],
            ['codigo' => '170302', 'descripcion' => 'Iberia', 'provincia_codigo' => '1703', 'departamento_codigo' => '17'],
            ['codigo' => '170303', 'descripcion' => 'Tahuamanu', 'provincia_codigo' => '1703', 'departamento_codigo' => '17'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // MOQUEGUA (18)
            // ================================

            // MARISCAL NIETO (1801)
            ['codigo' => '180101', 'descripcion' => 'Moquegua', 'provincia_codigo' => '1801', 'departamento_codigo' => '18'],
            ['codigo' => '180102', 'descripcion' => 'Carumas', 'provincia_codigo' => '1801', 'departamento_codigo' => '18'],
            ['codigo' => '180103', 'descripcion' => 'Cuchumbaya', 'provincia_codigo' => '1801', 'departamento_codigo' => '18'],
            ['codigo' => '180104', 'descripcion' => 'Samegua', 'provincia_codigo' => '1801', 'departamento_codigo' => '18'],
            ['codigo' => '180105', 'descripcion' => 'San Cristóbal', 'provincia_codigo' => '1801', 'departamento_codigo' => '18'],
            ['codigo' => '180106', 'descripcion' => 'Torata', 'provincia_codigo' => '1801', 'departamento_codigo' => '18'],

            // GENERAL SÁNCHEZ CERRO (1802)
            ['codigo' => '180201', 'descripcion' => 'Omate', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180202', 'descripcion' => 'Chojata', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180203', 'descripcion' => 'Coalaque', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180204', 'descripcion' => 'Ichuña', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180205', 'descripcion' => 'La Capilla', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180206', 'descripcion' => 'Lloque', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180207', 'descripcion' => 'Matalaque', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180208', 'descripcion' => 'Puquina', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180209', 'descripcion' => 'Quinistaquillas', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180210', 'descripcion' => 'Ubinas', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],
            ['codigo' => '180211', 'descripcion' => 'Yunga', 'provincia_codigo' => '1802', 'departamento_codigo' => '18'],

            // ILO (1803)
            ['codigo' => '180301', 'descripcion' => 'Ilo', 'provincia_codigo' => '1803', 'departamento_codigo' => '18'],
            ['codigo' => '180302', 'descripcion' => 'El Algarrobal', 'provincia_codigo' => '1803', 'departamento_codigo' => '18'],
            ['codigo' => '180303', 'descripcion' => 'Pacocha', 'provincia_codigo' => '1803', 'departamento_codigo' => '18'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // PASCO (19)
            // ================================

            // PASCO (1901)
            ['codigo' => '190101', 'descripcion' => 'Chaupimarca', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190102', 'descripcion' => 'Huachon', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190103', 'descripcion' => 'Huariaca', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190104', 'descripcion' => 'Huayllay', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190105', 'descripcion' => 'Ninacaca', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190106', 'descripcion' => 'Pallanchacra', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190107', 'descripcion' => 'Paucartambo', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190108', 'descripcion' => 'San Francisco de Asis de Yarusyacan', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190109', 'descripcion' => 'Simon Bolivar', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190110', 'descripcion' => 'Ticlacayan', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190111', 'descripcion' => 'Tinyahuarco', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190112', 'descripcion' => 'Vicco', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],
            ['codigo' => '190113', 'descripcion' => 'Yanacancha', 'provincia_codigo' => '1901', 'departamento_codigo' => '19'],

            // DANIEL ALCIDES CARRIÓN (1902)
            ['codigo' => '190201', 'descripcion' => 'Yanahuanca', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],
            ['codigo' => '190202', 'descripcion' => 'Chacayan', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],
            ['codigo' => '190203', 'descripcion' => 'Goyllarisquizga', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],
            ['codigo' => '190204', 'descripcion' => 'Paucar', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],
            ['codigo' => '190205', 'descripcion' => 'San Pedro de Pillao', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],
            ['codigo' => '190206', 'descripcion' => 'Santa Ana de Tusi', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],
            ['codigo' => '190207', 'descripcion' => 'Tapuc', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],
            ['codigo' => '190208', 'descripcion' => 'Vilcabamba', 'provincia_codigo' => '1902', 'departamento_codigo' => '19'],

            // OXAPAMPA (1903)
            ['codigo' => '190301', 'descripcion' => 'Oxapampa', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
            ['codigo' => '190302', 'descripcion' => 'Chontabamba', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
            ['codigo' => '190303', 'descripcion' => 'Huancabamba', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
            ['codigo' => '190304', 'descripcion' => 'Palcazu', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
            ['codigo' => '190305', 'descripcion' => 'Pozuzo', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
            ['codigo' => '190306', 'descripcion' => 'Puerto Bermudez', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
            ['codigo' => '190307', 'descripcion' => 'Villa Rica', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
            ['codigo' => '190308', 'descripcion' => 'Constitucion', 'provincia_codigo' => '1903', 'departamento_codigo' => '19'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // PIURA (20)
            // ================================

            // PIURA (2001)
            ['codigo' => '200101', 'descripcion' => 'Piura', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200104', 'descripcion' => 'Castilla', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200105', 'descripcion' => 'Catacaos', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200107', 'descripcion' => 'Cura Mori', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200108', 'descripcion' => 'El Tallan', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200109', 'descripcion' => 'La Arena', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200110', 'descripcion' => 'La Union', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200111', 'descripcion' => 'Las Lomas', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200114', 'descripcion' => 'Tambo Grande', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],
            ['codigo' => '200115', 'descripcion' => 'Veintiseis de Octubre', 'provincia_codigo' => '2001', 'departamento_codigo' => '20'],

            // AYABACA (2002)
            ['codigo' => '200201', 'descripcion' => 'Ayabaca', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200202', 'descripcion' => 'Frias', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200203', 'descripcion' => 'Jilili', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200204', 'descripcion' => 'Lagunas', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200205', 'descripcion' => 'Montero', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200206', 'descripcion' => 'Pacaipampa', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200207', 'descripcion' => 'Paimas', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200208', 'descripcion' => 'Sapillica', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200209', 'descripcion' => 'Sicchez', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],
            ['codigo' => '200210', 'descripcion' => 'Suyo', 'provincia_codigo' => '2002', 'departamento_codigo' => '20'],

            // HUANCABAMBA (2003)
            ['codigo' => '200301', 'descripcion' => 'Huancabamba', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],
            ['codigo' => '200302', 'descripcion' => 'Canchaque', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],
            ['codigo' => '200303', 'descripcion' => 'El Carmen de la Frontera', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],
            ['codigo' => '200304', 'descripcion' => 'Huarmaca', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],
            ['codigo' => '200305', 'descripcion' => 'Lalaquiz', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],
            ['codigo' => '200306', 'descripcion' => 'San Miguel de el Faique', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],
            ['codigo' => '200307', 'descripcion' => 'Sondor', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],
            ['codigo' => '200308', 'descripcion' => 'Sondorillo', 'provincia_codigo' => '2003', 'departamento_codigo' => '20'],

            // MORROPON (2004)
            ['codigo' => '200401', 'descripcion' => 'Chulucanas', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200402', 'descripcion' => 'Buenos Aires', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200403', 'descripcion' => 'Chalaco', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200404', 'descripcion' => 'La Matanza', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200405', 'descripcion' => 'Morropon', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200406', 'descripcion' => 'Salitral', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200407', 'descripcion' => 'San Juan de Bigote', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200408', 'descripcion' => 'Santa Catalina de Mossa', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200409', 'descripcion' => 'Santo Domingo', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],
            ['codigo' => '200410', 'descripcion' => 'Yamango', 'provincia_codigo' => '2004', 'departamento_codigo' => '20'],

            // PAITA (2005)
            ['codigo' => '200501', 'descripcion' => 'Paita', 'provincia_codigo' => '2005', 'departamento_codigo' => '20'],
            ['codigo' => '200502', 'descripcion' => 'Amotape', 'provincia_codigo' => '2005', 'departamento_codigo' => '20'],
            ['codigo' => '200503', 'descripcion' => 'Arenal', 'provincia_codigo' => '2005', 'departamento_codigo' => '20'],
            ['codigo' => '200504', 'descripcion' => 'Colan', 'provincia_codigo' => '2005', 'departamento_codigo' => '20'],
            ['codigo' => '200505', 'descripcion' => 'La Huaca', 'provincia_codigo' => '2005', 'departamento_codigo' => '20'],
            ['codigo' => '200506', 'descripcion' => 'Tamarindo', 'provincia_codigo' => '2005', 'departamento_codigo' => '20'],
            ['codigo' => '200507', 'descripcion' => 'Vichayal', 'provincia_codigo' => '2005', 'departamento_codigo' => '20'],

            // SECHURA (2006)
            ['codigo' => '200601', 'descripcion' => 'Sechura', 'provincia_codigo' => '2006', 'departamento_codigo' => '20'],
            ['codigo' => '200602', 'descripcion' => 'Bellavista de la Union', 'provincia_codigo' => '2006', 'departamento_codigo' => '20'],
            ['codigo' => '200603', 'descripcion' => 'Bernal', 'provincia_codigo' => '2006', 'departamento_codigo' => '20'],
            ['codigo' => '200604', 'descripcion' => 'Cristo nos Valga', 'provincia_codigo' => '2006', 'departamento_codigo' => '20'],
            ['codigo' => '200605', 'descripcion' => 'Vice', 'provincia_codigo' => '2006', 'departamento_codigo' => '20'],
            ['codigo' => '200606', 'descripcion' => 'Rinconada Llicuar', 'provincia_codigo' => '2006', 'departamento_codigo' => '20'],

            // SULLANA (2007)
            ['codigo' => '200701', 'descripcion' => 'Sullana', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],
            ['codigo' => '200702', 'descripcion' => 'Bellavista', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],
            ['codigo' => '200703', 'descripcion' => 'Ignacio Escudero', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],
            ['codigo' => '200704', 'descripcion' => 'Lancones', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],
            ['codigo' => '200705', 'descripcion' => 'Marcavelica', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],
            ['codigo' => '200706', 'descripcion' => 'Miguel Checa', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],
            ['codigo' => '200707', 'descripcion' => 'Querecotillo', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],
            ['codigo' => '200708', 'descripcion' => 'Salitral', 'provincia_codigo' => '2007', 'departamento_codigo' => '20'],

            // TALARA (2008)
            ['codigo' => '200801', 'descripcion' => 'Pariñas', 'provincia_codigo' => '2008', 'departamento_codigo' => '20'],
            ['codigo' => '200802', 'descripcion' => 'El Alto', 'provincia_codigo' => '2008', 'departamento_codigo' => '20'],
            ['codigo' => '200803', 'descripcion' => 'La Brea', 'provincia_codigo' => '2008', 'departamento_codigo' => '20'],
            ['codigo' => '200804', 'descripcion' => 'Lobitos', 'provincia_codigo' => '2008', 'departamento_codigo' => '20'],
            ['codigo' => '200805', 'descripcion' => 'Los Organos', 'provincia_codigo' => '2008', 'departamento_codigo' => '20'],
            ['codigo' => '200806', 'descripcion' => 'Mancora', 'provincia_codigo' => '2008', 'departamento_codigo' => '20'],

            // SECHURA / ZARUMILLA (Nota: Zarumilla pertenece a Tumbes, pero sigo el código 2009 proporcionado)
            ['codigo' => '200901', 'descripcion' => 'Zarumilla', 'provincia_codigo' => '2009', 'departamento_codigo' => '20'],
            ['codigo' => '200902', 'descripcion' => 'Aguas Verdes', 'provincia_codigo' => '2009', 'departamento_codigo' => '20'],
            ['codigo' => '200903', 'descripcion' => 'Matapalo', 'provincia_codigo' => '2009', 'departamento_codigo' => '20'],
            ['codigo' => '200904', 'descripcion' => 'Papayal', 'provincia_codigo' => '2009', 'departamento_codigo' => '20'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // PUNO (21)
            // ================================

            // PUNO (2101)
            ['codigo' => '210101', 'descripcion' => 'Puno', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210102', 'descripcion' => 'Acora', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210103', 'descripcion' => 'Amantani', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210104', 'descripcion' => 'Atuncolla', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210105', 'descripcion' => 'Capachica', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210106', 'descripcion' => 'Chucuito', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210107', 'descripcion' => 'Coata', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210108', 'descripcion' => 'Huata', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210109', 'descripcion' => 'Mañazo', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210110', 'descripcion' => 'Paucarcolla', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210111', 'descripcion' => 'Pichacani', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210112', 'descripcion' => 'Plateria', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210113', 'descripcion' => 'San Antonio', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210114', 'descripcion' => 'Tiquillaca', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],
            ['codigo' => '210115', 'descripcion' => 'Vilque', 'provincia_codigo' => '2101', 'departamento_codigo' => '21'],

            // AZANGARO (2102)
            ['codigo' => '210201', 'descripcion' => 'Azangaro', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210202', 'descripcion' => 'Achaya', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210203', 'descripcion' => 'Arapa', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210204', 'descripcion' => 'Asillo', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210205', 'descripcion' => 'Caminaca', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210206', 'descripcion' => 'Chupa', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210207', 'descripcion' => 'Jose Domingo Choquehuanca', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210208', 'descripcion' => 'Muñani', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210209', 'descripcion' => 'Potoni', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210210', 'descripcion' => 'Saman', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210211', 'descripcion' => 'San Anton', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210212', 'descripcion' => 'San Jose', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210213', 'descripcion' => 'San Juan de Salinas', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210214', 'descripcion' => 'Santiago de Pupuja', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],
            ['codigo' => '210215', 'descripcion' => 'Tirapata', 'provincia_codigo' => '2102', 'departamento_codigo' => '21'],

            // CARABAYA (2103)
            ['codigo' => '210301', 'descripcion' => 'Macusani', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210302', 'descripcion' => 'Ajoyani', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210303', 'descripcion' => 'Ayapata', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210304', 'descripcion' => 'Coasa', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210305', 'descripcion' => 'Corani', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210306', 'descripcion' => 'Crucero', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210307', 'descripcion' => 'Ituata', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210308', 'descripcion' => 'Ollachea', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210309', 'descripcion' => 'San Gaban', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],
            ['codigo' => '210310', 'descripcion' => 'Usicayos', 'provincia_codigo' => '2103', 'departamento_codigo' => '21'],

            // CHUCUITO (2104)
            ['codigo' => '210401', 'descripcion' => 'Juli', 'provincia_codigo' => '2104', 'departamento_codigo' => '21'],
            ['codigo' => '210402', 'descripcion' => 'Desaguadero', 'provincia_codigo' => '2104', 'departamento_codigo' => '21'],
            ['codigo' => '210403', 'descripcion' => 'Huacullani', 'provincia_codigo' => '2104', 'departamento_codigo' => '21'],
            ['codigo' => '210404', 'descripcion' => 'Kelluyo', 'provincia_codigo' => '2104', 'departamento_codigo' => '21'],
            ['codigo' => '210405', 'descripcion' => 'Pisacoma', 'provincia_codigo' => '2104', 'departamento_codigo' => '21'],
            ['codigo' => '210406', 'descripcion' => 'Pomata', 'provincia_codigo' => '2104', 'departamento_codigo' => '21'],
            ['codigo' => '210407', 'descripcion' => 'Zepita', 'provincia_codigo' => '2104', 'departamento_codigo' => '21'],

            // EL COLLAO (2105)
            ['codigo' => '210501', 'descripcion' => 'Ilave', 'provincia_codigo' => '2105', 'departamento_codigo' => '21'],
            ['codigo' => '210502', 'descripcion' => 'Capazo', 'provincia_codigo' => '2105', 'departamento_codigo' => '21'],
            ['codigo' => '210503', 'descripcion' => 'Pilcuyo', 'provincia_codigo' => '2105', 'departamento_codigo' => '21'],
            ['codigo' => '210504', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '2105', 'departamento_codigo' => '21'],
            ['codigo' => '210505', 'descripcion' => 'Conduriri', 'provincia_codigo' => '2105', 'departamento_codigo' => '21'],

            // HUANCANE (2106)
            ['codigo' => '210601', 'descripcion' => 'Huancane', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
            ['codigo' => '210602', 'descripcion' => 'Cojata', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
            ['codigo' => '210603', 'descripcion' => 'Huatasani', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
            ['codigo' => '210604', 'descripcion' => 'Inchupalla', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
            ['codigo' => '210605', 'descripcion' => 'Pusi', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
            ['codigo' => '210606', 'descripcion' => 'Rosaspata', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
            ['codigo' => '210607', 'descripcion' => 'Taraco', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
            ['codigo' => '210608', 'descripcion' => 'Vilque Chico', 'provincia_codigo' => '2106', 'departamento_codigo' => '21'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // SAN MARTÍN (22)
            // ================================

            // MOYOBAMBA (2201)
            ['codigo' => '220101', 'descripcion' => 'Moyobamba', 'provincia_codigo' => '2201', 'departamento_codigo' => '22'],
            ['codigo' => '220102', 'descripcion' => 'Calzada', 'provincia_codigo' => '2201', 'departamento_codigo' => '22'],
            ['codigo' => '220103', 'descripcion' => 'Habana', 'provincia_codigo' => '2201', 'departamento_codigo' => '22'],
            ['codigo' => '220104', 'descripcion' => 'Jepelacio', 'provincia_codigo' => '2201', 'departamento_codigo' => '22'],
            ['codigo' => '220105', 'descripcion' => 'Soritor', 'provincia_codigo' => '2201', 'departamento_codigo' => '22'],
            ['codigo' => '220106', 'descripcion' => 'Yantalo', 'provincia_codigo' => '2201', 'departamento_codigo' => '22'],

            // BELLAVISTA (2202)
            ['codigo' => '220201', 'descripcion' => 'Bellavista', 'provincia_codigo' => '2202', 'departamento_codigo' => '22'],
            ['codigo' => '220202', 'descripcion' => 'Alto Biavo', 'provincia_codigo' => '2202', 'departamento_codigo' => '22'],
            ['codigo' => '220203', 'descripcion' => 'Bajo Biavo', 'provincia_codigo' => '2202', 'departamento_codigo' => '22'],
            ['codigo' => '220204', 'descripcion' => 'Huallaga', 'provincia_codigo' => '2202', 'departamento_codigo' => '22'],
            ['codigo' => '220205', 'descripcion' => 'San Pablo', 'provincia_codigo' => '2202', 'departamento_codigo' => '22'],
            ['codigo' => '220206', 'descripcion' => 'San Rafael', 'provincia_codigo' => '2202', 'departamento_codigo' => '22'],

            // EL DORADO (2203)
            ['codigo' => '220301', 'descripcion' => 'San Jose de Sisa', 'provincia_codigo' => '2203', 'departamento_codigo' => '22'],
            ['codigo' => '220302', 'descripcion' => 'Agua Blanca', 'provincia_codigo' => '2203', 'departamento_codigo' => '22'],
            ['codigo' => '220303', 'descripcion' => 'San Martin', 'provincia_codigo' => '2203', 'departamento_codigo' => '22'],
            ['codigo' => '220304', 'descripcion' => 'Santa Rosa', 'provincia_codigo' => '2203', 'departamento_codigo' => '22'],
            ['codigo' => '220305', 'descripcion' => 'Shatoja', 'provincia_codigo' => '2203', 'departamento_codigo' => '22'],

            // HUALLAGA (2204)
            ['codigo' => '220401', 'descripcion' => 'Saposoa', 'provincia_codigo' => '2204', 'departamento_codigo' => '22'],
            ['codigo' => '220402', 'descripcion' => 'Alto Saposoa', 'provincia_codigo' => '2204', 'departamento_codigo' => '22'],
            ['codigo' => '220403', 'descripcion' => 'El Eslabon', 'provincia_codigo' => '2204', 'departamento_codigo' => '22'],
            ['codigo' => '220404', 'descripcion' => 'Piscoyacu', 'provincia_codigo' => '2204', 'departamento_codigo' => '22'],
            ['codigo' => '220405', 'descripcion' => 'Sacanche', 'provincia_codigo' => '2204', 'departamento_codigo' => '22'],
            ['codigo' => '220406', 'descripcion' => 'Tingo de Saposoa', 'provincia_codigo' => '2204', 'departamento_codigo' => '22'],

            // LAMAS (2205)
            ['codigo' => '220501', 'descripcion' => 'Lamas', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220502', 'descripcion' => 'Alonso de Alvarado', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220503', 'descripcion' => 'Barranquita', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220504', 'descripcion' => 'Caynarachi', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220505', 'descripcion' => 'Cuñumbuqui', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220506', 'descripcion' => 'Pinto Recodo', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220507', 'descripcion' => 'Rumisapa', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220508', 'descripcion' => 'San Roque de Cumbaza', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220509', 'descripcion' => 'Shanao', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220510', 'descripcion' => 'Tabalosos', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],
            ['codigo' => '220511', 'descripcion' => 'Zapatero', 'provincia_codigo' => '2205', 'departamento_codigo' => '22'],

            // MARISCAL CACERES (2206)
            ['codigo' => '220601', 'descripcion' => 'Juanjui', 'provincia_codigo' => '2206', 'departamento_codigo' => '22'],
            ['codigo' => '220602', 'descripcion' => 'Campanilla', 'provincia_codigo' => '2206', 'departamento_codigo' => '22'],
            ['codigo' => '220603', 'descripcion' => 'Huicungo', 'provincia_codigo' => '2206', 'departamento_codigo' => '22'],
            ['codigo' => '220604', 'descripcion' => 'Pachiza', 'provincia_codigo' => '2206', 'departamento_codigo' => '22'],
            ['codigo' => '220605', 'descripcion' => 'Pajarillo', 'provincia_codigo' => '2206', 'departamento_codigo' => '22'],

            // PICOTA (2207)
            ['codigo' => '220701', 'descripcion' => 'Picota', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220702', 'descripcion' => 'Buenos Aires', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220703', 'descripcion' => 'Caspisapa', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220704', 'descripcion' => 'Pilluana', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220705', 'descripcion' => 'Pucacaca', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220706', 'descripcion' => 'San Cristobal', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220707', 'descripcion' => 'San Hilarion', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220708', 'descripcion' => 'Shamboyacu', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220709', 'descripcion' => 'Tingo de Ponasa', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],
            ['codigo' => '220710', 'descripcion' => 'Tres Unidos', 'provincia_codigo' => '2207', 'departamento_codigo' => '22'],

            // RIOJA (2208)
            ['codigo' => '220801', 'descripcion' => 'Rioja', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220802', 'descripcion' => 'Awajun', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220803', 'descripcion' => 'Elias Soplin Vargas', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220804', 'descripcion' => 'Nueva Cajamarca', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220805', 'descripcion' => 'Pardo Miguel', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220806', 'descripcion' => 'Posic', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220807', 'descripcion' => 'San Fernando', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220808', 'descripcion' => 'Yorongos', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],
            ['codigo' => '220809', 'descripcion' => 'Yuracyacu', 'provincia_codigo' => '2208', 'departamento_codigo' => '22'],

            // SAN MARTIN (2209)
            ['codigo' => '220901', 'descripcion' => 'Tarapoto', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220902', 'descripcion' => 'Alberto Leveau', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220903', 'descripcion' => 'Cacatachi', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220904', 'descripcion' => 'Chazuta', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220905', 'descripcion' => 'Chipurana', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220906', 'descripcion' => 'El Porvenir', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220907', 'descripcion' => 'Huimbayoc', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220908', 'descripcion' => 'Juan Guerra', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220909', 'descripcion' => 'La Banda de Shilcayo', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220910', 'descripcion' => 'Morales', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220911', 'descripcion' => 'Papaplaya', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220912', 'descripcion' => 'San Antonio', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220913', 'descripcion' => 'Sauce', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],
            ['codigo' => '220914', 'descripcion' => 'Shapaja', 'provincia_codigo' => '2209', 'departamento_codigo' => '22'],

            // TOCACHE (2210)
            ['codigo' => '221001', 'descripcion' => 'Tocache', 'provincia_codigo' => '2210', 'departamento_codigo' => '22'],
            ['codigo' => '221002', 'descripcion' => 'Nuevo Progreso', 'provincia_codigo' => '2210', 'departamento_codigo' => '22'],
            ['codigo' => '221003', 'descripcion' => 'Polvora', 'provincia_codigo' => '2210', 'departamento_codigo' => '22'],
            ['codigo' => '221004', 'descripcion' => 'Shunte', 'provincia_codigo' => '2210', 'departamento_codigo' => '22'],
            ['codigo' => '221005', 'descripcion' => 'Uchiza', 'provincia_codigo' => '2210', 'departamento_codigo' => '22'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // TACNA (23)
            // ================================

            // TACNA (2301)
            ['codigo' => '230101', 'descripcion' => 'Tacna', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230102', 'descripcion' => 'Alto de la Alianza', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230103', 'descripcion' => 'Calana', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230104', 'descripcion' => 'Ciudad Nueva', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230105', 'descripcion' => 'Inclan', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230106', 'descripcion' => 'Pachia', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230107', 'descripcion' => 'Palca', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230108', 'descripcion' => 'Pocollay', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230109', 'descripcion' => 'Sama', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],
            ['codigo' => '230110', 'descripcion' => 'Coronel Gregorio Albarracin Lanchipa', 'provincia_codigo' => '2301', 'departamento_codigo' => '23'],

            // CANDARAVE (2302)
            ['codigo' => '230201', 'descripcion' => 'Candarave', 'provincia_codigo' => '2302', 'departamento_codigo' => '23'],
            ['codigo' => '230202', 'descripcion' => 'Cairani', 'provincia_codigo' => '2302', 'departamento_codigo' => '23'],
            ['codigo' => '230203', 'descripcion' => 'Camilaca', 'provincia_codigo' => '2302', 'departamento_codigo' => '23'],
            ['codigo' => '230204', 'descripcion' => 'Curibaya', 'provincia_codigo' => '2302', 'departamento_codigo' => '23'],
            ['codigo' => '230205', 'descripcion' => 'Huanuara', 'provincia_codigo' => '2302', 'departamento_codigo' => '23'],
            ['codigo' => '230206', 'descripcion' => 'Quilahuani', 'provincia_codigo' => '2302', 'departamento_codigo' => '23'],

            // JORGE BASADRE (2303)
            ['codigo' => '230301', 'descripcion' => 'Locumba', 'provincia_codigo' => '2303', 'departamento_codigo' => '23'],
            ['codigo' => '230302', 'descripcion' => 'Ilabaya', 'provincia_codigo' => '2303', 'departamento_codigo' => '23'],
            ['codigo' => '230303', 'descripcion' => 'Ite', 'provincia_codigo' => '2303', 'departamento_codigo' => '23'],

            // TARATA (2304)
            ['codigo' => '230401', 'descripcion' => 'Tarata', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
            ['codigo' => '230402', 'descripcion' => 'Heroes Albarracin', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
            ['codigo' => '230403', 'descripcion' => 'Estique', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
            ['codigo' => '230404', 'descripcion' => 'Estique Pampa', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
            ['codigo' => '230405', 'descripcion' => 'Sitajara', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
            ['codigo' => '230406', 'descripcion' => 'Susapaya', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
            ['codigo' => '230407', 'descripcion' => 'Tarucachi', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
            ['codigo' => '230408', 'descripcion' => 'Ticaco', 'provincia_codigo' => '2304', 'departamento_codigo' => '23'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // TUMBES (24)
            // ================================

            // TUMBES (2401)
            ['codigo' => '240101', 'descripcion' => 'Tumbes', 'provincia_codigo' => '2401', 'departamento_codigo' => '24'],
            ['codigo' => '240102', 'descripcion' => 'Corrales', 'provincia_codigo' => '2401', 'departamento_codigo' => '24'],
            ['codigo' => '240103', 'descripcion' => 'La Cruz', 'provincia_codigo' => '2401', 'departamento_codigo' => '24'],
            ['codigo' => '240104', 'descripcion' => 'Pampas de Hospital', 'provincia_codigo' => '2401', 'departamento_codigo' => '24'],
            ['codigo' => '240105', 'descripcion' => 'San Jacinto', 'provincia_codigo' => '2401', 'departamento_codigo' => '24'],
            ['codigo' => '240106', 'descripcion' => 'San Juan de la Virgen', 'provincia_codigo' => '2401', 'departamento_codigo' => '24'],

            // CONTRALMIRANTE VILLAR (2402)
            ['codigo' => '240201', 'descripcion' => 'Zorritos', 'provincia_codigo' => '2402', 'departamento_codigo' => '24'],
            ['codigo' => '240202', 'descripcion' => 'Casitas', 'provincia_codigo' => '2402', 'departamento_codigo' => '24'],
            ['codigo' => '240203', 'descripcion' => 'Canoas de Punta Sal', 'provincia_codigo' => '2402', 'departamento_codigo' => '24'],

            // ZARUMILLA (2403)
            ['codigo' => '240301', 'descripcion' => 'Zarumilla', 'provincia_codigo' => '2403', 'departamento_codigo' => '24'],
            ['codigo' => '240302', 'descripcion' => 'Aguas Verdes', 'provincia_codigo' => '2403', 'departamento_codigo' => '24'],
            ['codigo' => '240303', 'descripcion' => 'Matapalo', 'provincia_codigo' => '2403', 'departamento_codigo' => '24'],
            ['codigo' => '240304', 'descripcion' => 'Papayal', 'provincia_codigo' => '2403', 'departamento_codigo' => '24'],
        ]);

        $districts = array_merge($districts, [

            // ================================
            // UCAYALI (25)
            // ================================

            // CORONEL PORTILLO (2501)
            ['codigo' => '250101', 'descripcion' => 'Calleria', 'provincia_codigo' => '2501', 'departamento_codigo' => '25'],
            ['codigo' => '250102', 'descripcion' => 'Campoverde', 'provincia_codigo' => '2501', 'departamento_codigo' => '25'],
            ['codigo' => '250103', 'descripcion' => 'Iparia', 'provincia_codigo' => '2501', 'departamento_codigo' => '25'],
            ['codigo' => '250104', 'descripcion' => 'Masisea', 'provincia_codigo' => '2501', 'departamento_codigo' => '25'],
            ['codigo' => '250105', 'descripcion' => 'Yarinacocha', 'provincia_codigo' => '2501', 'departamento_codigo' => '25'],
            ['codigo' => '250106', 'descripcion' => 'Nueva Requena', 'provincia_codigo' => '2501', 'departamento_codigo' => '25'],
            ['codigo' => '250107', 'descripcion' => 'Manantay', 'provincia_codigo' => '2501', 'departamento_codigo' => '25'],

            // ATALAYA (2502)
            ['codigo' => '250201', 'descripcion' => 'Raymondi', 'provincia_codigo' => '2502', 'departamento_codigo' => '25'],
            ['codigo' => '250202', 'descripcion' => 'Sepahua', 'provincia_codigo' => '2502', 'departamento_codigo' => '25'],
            ['codigo' => '250203', 'descripcion' => 'Tahuania', 'provincia_codigo' => '2502', 'departamento_codigo' => '25'],
            ['codigo' => '250204', 'descripcion' => 'Yurua', 'provincia_codigo' => '2502', 'departamento_codigo' => '25'],

            // PADRE ABAD (2503)
            ['codigo' => '250301', 'descripcion' => 'Padre Abad', 'provincia_codigo' => '2503', 'departamento_codigo' => '25'],
            ['codigo' => '250302', 'descripcion' => 'Irazola', 'provincia_codigo' => '2503', 'departamento_codigo' => '25'],
            ['codigo' => '250303', 'descripcion' => 'Curimana', 'provincia_codigo' => '2503', 'departamento_codigo' => '25'],
            ['codigo' => '250304', 'descripcion' => 'Neshuya', 'provincia_codigo' => '2503', 'departamento_codigo' => '25'],
            ['codigo' => '250305', 'descripcion' => 'Alexander Von Humboldt', 'provincia_codigo' => '2503', 'departamento_codigo' => '25'],

            // PURUS (2504)
            ['codigo' => '250401', 'descripcion' => 'Purus', 'provincia_codigo' => '2504', 'departamento_codigo' => '25'],
        ]);

        foreach ($districts as $district) {
            DB::table('districts')->updateOrInsert(
                ['codigo' => $district['codigo']],
                $district
            );
        }
    }
}
