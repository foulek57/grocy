<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

//include_file('desktop', 'add', 'css', 'grocy');
include_file('desktop', 'panel/add2grocy', 'js', 'grocy');

$eqlogicid = init('eqlogicid');
$eqLogic   = eqLogic::byId( $eqlogicid );
$product   = $eqLogic->getConfiguration('openfoodfacts');
$cache     = utils::o2a( cache::byKey('grocy::cache') );

?>
<div class="row row-overflow">
    <div class="col-xs-12" id="div_resumePluginList">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fas fa-cogs"></i> {{Ajouter un produit à Grocy}}</h3>
            </div>
            <div class="panel-body">
                <form id="createProductInGrocy" class="form-horizontal">
                    <input type="hidden" name="eqlogicid" value="<?php echo $eqlogicid; ?>" />
                    <fieldset>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Nom du produit}}</label>
                            <div class="col-lg-8">
                                <select class="form-control" name="name">
                                    <option value="<?php echo  $product['product_name']; ?>"><?php echo  $product['product_name']; ?></option>
                                    <option value="<?php echo  $product['generic_name_fr']; ?>"><?php echo  $product['generic_name_fr']; ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Description}}</label>
                            <div class="col-lg-8">
                                <textarea name="description" type="text" class="form-control" style="height: 150px;"><?php echo  $product['ingredients_text']; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Code barre}}</label>
                            <div class="col-lg-8">
                                <input type="text" name="barcode" class="form-control" value="<?php echo  $product['code']; ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Emplacement}}</label>
                            <div class="col-lg-8">
                                <select name="location_id" id="location" class="form-control">
                                <?php
									foreach( $cache['value']['locations'] as $location ) {
										echo '<option value="' . $location['id'] . '">' . $location['name'] . '</option>';
									}
								?>                                
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Groupe de produit}}</label>
                            <div class="col-lg-8">
                                <select name="product_group_id" id="productGroups" class="form-control">
                                    <option value="0">Aucun</option>
                                    <?php
									foreach( $cache['value']['productGroups'] as $productGroups ) {
										echo '<option value="' . $productGroups['id'] . '">' . $productGroups['name'] . '</option>';
									}
								    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Format à l'achat}}</label>
                            <div class="col-lg-8">
                                <select name="qu_id_purchase" id="qteUnitsPurchase" class="form-control">
                                <?php
									foreach( $cache['value']['units'] as $unit ) {
										echo '<option value="' . $unit['id'] . '">' . $unit['name'] . '</option>';
									}
								?>                            
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Format au stockage}}</label>
                            <div class="col-lg-8">
                                <select name="qu_id_stock" id="qteUnitsStock" class="form-control">
                                <?php
									foreach( $cache['value']['units'] as $unit ) {
										echo '<option value="' . $unit['id'] . '">' . $unit['name'] . '</option>';
									}
								?>  
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Facteur entre la quantité à l'achat et la quantité en stock  }}</label>
                            <div class="col-lg-8">
                                <input type="text" name="qu_factor_purchase_to_stock" class="form-control" value="1" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Quantité}}</label>
                            <div class="col-lg-8">
                                <input type="text" name="quantity" class="form-control" value="<?php echo init( 'qte' ); ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Jours avant péremption par défaut (DLC)}} <sup><i class="fas fa-question-circle tooltipstered" tooltip="{{A l'achat, ce nombre de jours sera ajouté à la date de péremption suggérée (-1 implique que ce produit ne périme jamais)}}"></i></sup></label>
                            <div class="col-lg-8">
                                <input type="text" name="default_best_before_days" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Date de péremption en jours par défaut après ouverture (DLC)}} <sup><i class="fas fa-question-circle tooltipstered" tooltip="{{Quand un produit est marqué comme ouvert, la date de péremption sera remplacée par la date du jour + ce nombre de jours (une valeur de 0 désactive ce changement)}}"></i></sup></label>
                            <div class="col-lg-8">
                                <input type="text" name="default_best_before_days_after_open" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">{{Calories}}</label>
                            <div class="col-lg-8">
                                <input type="text" name="calories"  class="form-control" value="<?php echo  $product['nutriments']['energy_value']; ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-4"></div>
                            <div class="col-lg-8">
                                <a id="bt_createProductInGrocy" class="btn btn-info form-control">{{Créer le produit}}</a>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>