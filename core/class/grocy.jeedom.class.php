<?php
/* 
 * Author: Johan VIVIEN
 * Since: 1.0
 *
*/

class grocy_extend {
    /*     * *************************Attributs******************************* */
    private $product_id;
    private $barcode;    
    private $eqlogic_id;

    /*     * ***********************Methode static**************************** */
    public static function byBarcode($_barcode) {
        $values = array(
        'barcode' => $_barcode,
        );
        $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
        FROM grocy_extend
        WHERE barcode=:barcode';
        return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
    }


    /*     * *********************Méthodes d'instance************************* */

    public function save() {
        return DB::save($this);
    }

    public function remove() {
        return DB::remove($this);
    }

    /*     * **********************Getteur Setteur**************************** */
	public function getProduct_id() {
		return $this->product_id;
    }
	public function setProduct_id($_product_id) {
		$this->product_id = $_product_id;
    }
	public function getBarcode() {
		return $this->barcode;
	}
	public function setBarcode($_barcode) {
		$this->barcode = $_barcode;
    }
	public function getEqlogic_id() {
		return $this->eqlogic_id;
	}
	public function setEqlogic_id($_eqlogic_id) {
		$this->eqlogic_id = $_eqlogic_id;
    }
}