<?php

require_once dirname(__FILE__) . "/ProductStock.php";
class BigBuyCatalog
{
    const CLASS_LOG = "BigBuy";
    const CLASS_LOG_CRON = "BigBuy Cron";

    /**
     * Summary of updatePriceAndQuantity
     * @return bool
     */
    public function updatePriceAndQuantity()
    {
        
        PrestaShopLogger::AddLog('BigBuy products : Start update price and quantity.', 3, self::CLASS_LOG_CRON);

        $files = $this->getUpdatedData();
        $feature_id = Configuration::get('ADD_FEATURE_KEY_BB', false);

        var_export($files);

        $productStocks = [];
        foreach ($files as $file) {
            $productStocks[] = new ProductStock(
                $file['sku'],
                $file['stocks'][0]['quantity'],
                BigBuyTvs::getSendType($file['stocks'][0]['maxHandlingDays'])
            );
        }



        PrestaShopLogger::AddLog('BigBuy products : end copy file and dispatch file', 3, self::CLASS_LOG_CRON);

        PrestaShopLogger::AddLog('BigBuy products : start import file by file' . 'nb_files=> ' . count($files), 3, self::CLASS_LOG_CRON);

        // update stock to 0
        $sql = "
                    UPDATE  ps_product p
                    INNER JOIN ps_stock_available sa on sa.id_product = p.id_product and sa.quantity <> 0
                    SET sa.quantity = 0, p.date_upd = now()
                    WHERE id_supplier = " . Configuration::get('BIGBUY_ID_SUPPLIER');

        Db::getInstance()->execute($sql);


        //mise a jour de prix et quantité
        foreach ($productStocks as  $product) {
            $productId = Product::getIdByReference(BigBuyTvs::addRefPrefix($product->getSkuProduct()));
            //var_export($productId);
            $prestashopProduct = new Product($productId);
            if (Validate::isLoadedObject($prestashopProduct)) {

                echo "Product  Load with ID $productId \n";

                // $prestashopProduct->price = Tools::ps_round($price, 2);
                //$prestashopProduct->wholesale_price = Tools::ps_round($wholesale_price, 2);

                try {

                    $prestashopProduct->save();

                    StockAvailable::setQuantity($prestashopProduct->id, 0, $product->getStock());

                    if ($feature_id) {
                        $product->addFeaturesToDB($feature_id, array($feature_id => $product->getSendType()));
                    }

                    echo "Product save " . json_encode([
                        'id' => $prestashopProduct->id,
                        //'price' => $price,
                        'reference' => $product->getSkuProduct(),
                        'quantity' => $product->getStock(),
                    ]) . " \n";

                    PrestaShopLogger::AddLog('Bigbuy Update Product', 3, self::CLASS_LOG_CRON);
                } catch (Exception $e) {
                    PrestaShopLogger::AddLog('Bigbuy Unable to update product' . 'nb_files=> ', 3, self::CLASS_LOG_CRON);
                }
            }


            PrestaShopLogger::AddLog('bigbuy products : end import file by file' . 'nb_files=> ' . count($files), 3, self::CLASS_LOG_CRON);

            $files = array();
        }

        return true;
    }
    /**
     * Summary of getUpdatedData
     * @return mixed
     */
    public function getUpdatedData()
    {

        $data = '';
        $tab = '';

        // URL de l'API
        $url = BigBuyTvs::API_BASE_URL . '/rest/catalog/productsstockbyreference.json';

        // Données du corps de la requête

        $data = '{
                    "product_stock_request": {
                        "products": [
                        {
                            "sku": "F1515101" 
                        },{
                            "sku":"H2500108"
                        }

                        ] 
                        } 
                }';
        $data_v = '{
                    "product_stock_request": {
                        "products": [
                        {
                            "sku": "S7903575" 
                        },
                        {
                            "sku": "S7185366" 
                        },
                        {
                            "sku": "S6501057" 
                        },
                        {
                            "sku": "S7907237" 
                        },
                        {
                            "sku": "S7911836" 
                        },
                        {
                            "sku": "V1704791" 
                        },
                        {
                            "sku": "S7920337" 
                        },
                        {
                            "sku": "S37113839" 
                        },
                        {
                            "sku": "S7910095" 
                        },
                        {
                            "sku": "S7902085" 
                        },
                        {
                            "sku": "S6501202" 
                        },
                        {
                            "sku": "S7917513" 
                        },
                        {
                            "sku": "V1706814" 
                        },
                        {
                            "sku": "S7904573" 
                        },
                        {
                            "sku": "S7904568" 
                        },
                        {
                            "sku": "S3016248" 
                        },
                        {
                            "sku": "S2209819" 
                        },
                        {
                            "sku": "S7901928" 
                        },
                        {
                            "sku": "S7908031" 
                        },
                        {
                            "sku": "S7904570" 
                        },
                        {
                            "sku": "S7914139" 
                        },
                        {
                            "sku": "S7908532" 
                        },
                        {
                            "sku": "S7902440" 
                        },
                        {
                            "sku": "S7904564" 
                        },
                        {
                            "sku": "S6504058" 
                        },
                        {
                            "sku": "S6500938" 
                        },
                        {
                            "sku": "S7915090" 
                        },
                        {
                            "sku": "S7113844" 
                        },
                        {
                            "sku": "S6500415" 
                        },
                        {
                            "sku": "S1906004" 
                        },
                        {
                            "sku": "S7906459" 
                        },
                        {
                            "sku": "S7917809" 
                        },
                        {
                            "sku": "S7918826" 
                        },
                        {
                            "sku": "S7905416" 
                        },
                        {
                            "sku": "S7903836" 
                        },
                        {
                            "sku": "S6501009" 
                        },
                        {
                            "sku": "S7910082" 
                        },
                        {
                            "sku": "S7910726" 
                        },
                        {
                            "sku": "S7910083" 
                        },
                        {
                            "sku": "S7909258" 
                        },
                        {
                            "sku": "S7911822" 
                        },
                        {
                            "sku": "S2211860" 
                        },
                        {
                            "sku": "S7900076" 
                        },
                        {
                            "sku": "S7900565" 
                        },
                        {
                            "sku": "S7902436" 
                        },
                        {
                            "sku": "S1906003" 
                        },
                        {
                            "sku": "S7911649" 
                        },
                        {
                            "sku": "S7917765" 
                        },
                        {
                            "sku": "S0407506" 
                        },
                        {
                            "sku": "S7910096" 
                        },
                        {
                            "sku": "S7913000" 
                        },
                        {
                            "sku": "S7105775" 
                        },
                        {
                            "sku": "S1906028" 
                        },
                        {
                            "sku": "S7904808" 
                        },
                        {
                            "sku": "S7913482" 
                        },
                        {
                            "sku": "S6503816" 
                        },
                        {
                            "sku": "S7918419" 
                        },
                        {
                            "sku": "S7904562" 
                        },
                        {
                            "sku": "S6500841" 
                        },
                        {
                            "sku": "S7904809" 
                        },
                        {
                            "sku": "V0103132" 
                        },
                        {
                            "sku": "S7900566" 
                        },
                        {
                            "sku": "S7917278" 
                        },
                        {
                            "sku": "S7903518" 
                        },
                        {
                            "sku": "S7908485" 
                        },
                        {
                            "sku": "S6500245" 
                        },
                        {
                            "sku": "S7912300" 
                        },
                        {
                            "sku": "S7919952" 
                        },
                        {
                            "sku": "S7903841" 
                        },
                        {
                            "sku": "S7917247" 
                        },
                        {
                            "sku": "S1906027" 
                        },
                        {
                            "sku": "S55167058" 
                        },
                        {
                            "sku": "S6500394" 
                        },
                        {
                            "sku": "S7906744" 
                        },
                        {
                            "sku": "S7903576" 
                        },
                        {
                            "sku": "S3727093" 
                        },
                        {
                            "sku": "S7909346" 
                        },
                        {
                            "sku": "S7913388" 
                        },
                        {
                            "sku": "S2205899" 
                        },
                        {
                            "sku": "S6501018" 
                        },
                        {
                            "sku": "S7911901" 
                        },
                        {
                            "sku": "S7912128" 
                        },
                        {
                            "sku": "S6504096" 
                        },
                        {
                            "sku": "S6500069" 
                        },
                        {
                            "sku": "S2209524" 
                        },
                        {
                            "sku": "S7905588" 
                        },
                        {
                            "sku": "S7910087" 
                        },
                        {
                            "sku": "V1706812" 
                        },
                        {
                            "sku": "S7917281" 
                        },
                        {
                            "sku": "S7919958" 
                        },
                        {
                            "sku": "S7906917" 
                        },
                        {
                            "sku": "S7918393" 
                        },
                        {
                            "sku": "S7913385" 
                        },
                        {
                            "sku": "S7900567" 
                        },
                        {
                            "sku": "S7909259" 
                        },
                        {
                            "sku": "S2200353" 
                        },
                        {
                            "sku": "S7905590" 
                        },
                        {
                            "sku": "S7907607" 
                        },
                        {
                            "sku": "V1706811" 
                        },
                        {
                            "sku": "S7909624" 
                        },
                        {
                            "sku": "S7902083" 
                        },
                        {
                            "sku": "S6500977" 
                        },
                        {
                            "sku": "S37113842" 
                        },
                        {
                            "sku": "S7906687" 
                        },
                        {
                            "sku": "V1706805" 
                        },
                        {
                            "sku": "S6502725" 
                        },
                        {
                            "sku": "S7904282" 
                        },
                        {
                            "sku": "S7908123" 
                        },
                        {
                            "sku": "S2205906" 
                        },
                        {
                            "sku": "S2200355" 
                        },
                        {
                            "sku": "S7916532" 
                        },
                        {
                            "sku": "S7106457" 
                        },
                        {
                            "sku": "S7914805" 
                        },
                        {
                            "sku": "V1706810" 
                        },
                        {
                            "sku": "S2211005" 
                        },
                        {
                            "sku": "S6503815" 
                        },
                        {
                            "sku": "S7917695" 
                        },
                        {
                            "sku": "V1706807" 
                        },
                        {
                            "sku": "S7911454" 
                        },
                        {
                            "sku": "S7903840" 
                        },
                        {
                            "sku": "S7904819" 
                        },
                        {
                            "sku": "S6500819" 
                        },
                        {
                            "sku": "S7910102" 
                        },
                        {
                            "sku": "S7113837" 
                        },
                        {
                            "sku": "S7919026" 
                        },
                        {
                            "sku": "S6501210" 
                        },
                        {
                            "sku": "S7901340" 
                        },
                        {
                            "sku": "S6500849" 
                        },
                        {
                            "sku": "S7904448" 
                        },
                        {
                            "sku": "V1706808" 
                        },
                        {
                            "sku": "S7918421" 
                        },
                        {
                            "sku": "S7601948" 
                        },
                        {
                            "sku": "S7913420" 
                        },
                        {
                            "sku": "S2208011" 
                        },
                        {
                            "sku": "V1706804" 
                        },
                        {
                            "sku": "S7903872" 
                        },
                        {
                            "sku": "V1706768" 
                        },
                        {
                            "sku": "S7901923" 
                        },
                        {
                            "sku": "S7903839" 
                        },
                        {
                            "sku": "S7918996" 
                        },
                        {
                            "sku": "S7915939" 
                        },
                        {
                            "sku": "S7910358" 
                        },
                        {
                            "sku": "S7900818" 
                        },
                        {
                            "sku": "S7908030" 
                        },
                        {
                            "sku": "V1706800" 
                        },
                        {
                            "sku": "S7913386" 
                        },
                        {
                            "sku": "V0103527" 
                        },
                        {
                            "sku": "S6502726" 
                        },
                        {
                            "sku": "S7919842" 
                        },
                        {
                            "sku": "S7901932" 
                        },
                        {
                            "sku": "S7113846" 
                        },
                        {
                            "sku": "S7918823" 
                        },
                        {
                            "sku": "S7912306" 
                        },
                        {
                            "sku": "S6500063" 
                        },
                        {
                            "sku": "S8423355" 
                        },
                        {
                            "sku": "S7906860" 
                        },
                        {
                            "sku": "S6503007" 
                        },
                        {
                            "sku": "S6501140" 
                        },
                        {
                            "sku": "S6501579" 
                        },
                        {
                            "sku": "S7910079" 
                        },
                        {
                            "sku": "S7901476" 
                        },
                        {
                            "sku": "S7601947" 
                        },
                        {
                            "sku": "S7919025" 
                        },
                        {
                            "sku": "S7905488" 
                        },
                        {
                            "sku": "S6500070" 
                        },
                        {
                            "sku": "S2209647" 
                        },
                        {
                            "sku": "S2200354" 
                        },
                        {
                            "sku": "S6501049" 
                        },
                        {
                            "sku": "S7905750" 
                        },
                        {
                            "sku": "S7917280" 
                        },
                        {
                            "sku": "S5615725" 
                        },
                        {
                            "sku": "S6503817" 
                        },
                        {
                            "sku": "V1706793" 
                        },
                        {
                            "sku": "S7917810" 
                        },
                        {
                            "sku": "S7900568" 
                        },
                        {
                            "sku": "V1706795" 
                        },
                        {
                            "sku": "S6501055" 
                        },
                        {
                            "sku": "S7915250" 
                        },
                        {
                            "sku": "S7917078" 
                        },
                        {
                            "sku": "S6503814" 
                        },
                        {
                            "sku": "S7108954" 
                        },
                        {
                            "sku": "S7919951" 
                        },
                        {
                            "sku": "S1903337" 
                        },
                        {
                            "sku": "S6501203" 
                        },
                        {
                            "sku": "S7738867" 
                        },
                        {
                            "sku": "S7905688" 
                        },
                        {
                            "sku": "S7905215" 
                        },
                        {
                            "sku": "S7906690" 
                        },
                        {
                            "sku": "S7902438" 
                        },
                        {
                            "sku": "S6500846" 
                        },
                        {
                            "sku": "S7153915" 
                        },
                        {
                            "sku": "S7919968" 
                        },
                        {
                            "sku": "S7920318" 
                        },
                        {
                            "sku": "S7919881" 
                        },
                        {
                            "sku": "S7902086" 
                        },
                        {
                            "sku": "S7906562" 
                        },
                        {
                            "sku": "S2211051" 
                        },
                        {
                            "sku": "S7910088" 
                        },
                        {
                            "sku": "S7903869" 
                        },
                        {
                            "sku": "S37113840" 
                        },
                        {
                            "sku": "S7915092" 
                        },
                        {
                            "sku": "S6500951" 
                        },
                        {
                            "sku": "S6500556" 
                        },
                        {
                            "sku": "S7901478" 
                        },
                        {
                            "sku": "S6500068" 
                        },
                        {
                            "sku": "S3607480" 
                        },
                        {
                            "sku": "S7901471" 
                        },
                        {
                            "sku": "S7912384" 
                        },
                        {
                            "sku": "S7917119" 
                        },
                        {
                            "sku": "S7912352" 
                        },
                        {
                            "sku": "S7901074" 
                        },
                        {
                            "sku": "S7919841" 
                        },
                        {
                            "sku": "S6502877" 
                        },
                        {
                            "sku": "S7903570" 
                        },
                        {
                            "sku": "S7910081" 
                        },
                        {
                            "sku": "S7901472" 
                        },
                        {
                            "sku": "S7910098" 
                        },
                        {
                            "sku": "S7917760" 
                        },
                        {
                            "sku": "S7152249" 
                        },
                        {
                            "sku": "S7900313" 
                        },
                        {
                            "sku": "V1704713" 
                        },
                        {
                            "sku": "S7914790" 
                        },
                        {
                            "sku": "S7916535" 
                        },
                        {
                            "sku": "S7910086" 
                        },
                        {
                            "sku": "S7902854" 
                        },
                        {
                            "sku": "S0431179" 
                        },
                        {
                            "sku": "S7912122" 
                        },
                        {
                            "sku": "S7901933" 
                        },
                        {
                            "sku": "S6500911" 
                        },
                        {
                            "sku": "S7907517" 
                        },
                        {
                            "sku": "S7918998" 
                        },
                        {
                            "sku": "S7903577" 
                        },
                        {
                            "sku": "S3727094" 
                        },
                        {
                            "sku": "S37112509" 
                        },
                        {
                            "sku": "S7917841" 
                        },
                        {
                            "sku": "S7172920" 
                        },
                        {
                            "sku": "S6500071" 
                        },
                        {
                            "sku": "S7919001" 
                        },
                        {
                            "sku": "S7917763" 
                        },
                        {
                            "sku": "S37113841" 
                        },
                        {
                            "sku": "S7904810" 
                        },
                        {
                            "sku": "V1706794" 
                        },
                        {
                            "sku": "S7913404" 
                        },
                        {
                            "sku": "S7605968" 
                        },
                        {
                            "sku": "S7913410" 
                        },
                        {
                            "sku": "S37112508" 
                        },
                        {
                            "sku": "S7903571" 
                        },
                        {
                            "sku": "S7113848" 
                        },
                        {
                            "sku": "S7919840" 
                        },
                        {
                            "sku": "S7903354" 
                        },
                        {
                            "sku": "S7914791" 
                        },
                        {
                            "sku": "S7900315" 
                        },
                        {
                            "sku": "S6500246" 
                        },
                        {
                            "sku": "S7911055" 
                        },
                        {
                            "sku": "S7917759" 
                        },
                        {
                            "sku": "S7901924" 
                        },
                        {
                            "sku": "S7905484" 
                        },
                        {
                            "sku": "S7912187" 
                        },
                        {
                            "sku": "S7903569" 
                        },
                        {
                            "sku": "S7915089" 
                        },
                        {
                            "sku": "S6500322" 
                        },
                        {
                            "sku": "S7906460" 
                        },
                        {
                            "sku": "S7920202" 
                        },
                        {
                            "sku": "S7904499" 
                        },
                        {
                            "sku": "S7911653" 
                        },
                        {
                            "sku": "S7919961" 
                        },
                        {
                            "sku": "S2205907" 
                        },
                        {
                            "sku": "S2200352" 
                        },
                        {
                            "sku": "S7910101" 
                        },
                        {
                            "sku": "S7916371" 
                        },
                        {
                            "sku": "S2205712" 
                        },
                        {
                            "sku": "S7917804" 
                        },
                        {
                            "sku": "S6500315" 
                        },
                        {
                            "sku": "S7910840" 
                        },
                        {
                            "sku": "S7919818" 
                        },
                        {
                            "sku": "S7916997" 
                        },
                        {
                            "sku": "S7906859" 
                        },
                        {
                            "sku": "S7917762" 
                        },
                        {
                            "sku": "S7900564" 
                        },
                        {
                            "sku": "S6500065" 
                        },
                        {
                            "sku": "S7911904" 
                        },
                        {
                            "sku": "S7913481" 
                        },
                        {
                            "sku": "S7920322" 
                        },
                        {
                            "sku": "S3727092" 
                        },
                        {
                            "sku": "S7905592" 
                        },
                        {
                            "sku": "S7910367" 
                        },
                        {
                            "sku": "S7914091" 
                        },
                        {
                            "sku": "S7920344" 
                        },
                        {
                            "sku": "S7913480" 
                        },
                        {
                            "sku": "S7917891" 
                        },
                        {
                            "sku": "S2208154" 
                        },
                        {
                            "sku": "S3607479" 
                        },
                        {
                            "sku": "S7911648" 
                        },
                        {
                            "sku": "S7919425" 
                        },
                        {
                            "sku": "S6500391" 
                        },
                        {
                            "sku": "S7709136" 
                        },
                        {
                            "sku": "V0103677" 
                        },
                        {
                            "sku": "S7911775" 
                        },
                        {
                            "sku": "S7919031" 
                        },
                        {
                            "sku": "S6503190" 
                        },
                        {
                            "sku": "S6500969" 
                        },
                        {
                            "sku": "V1706802" 
                        },
                        {
                            "sku": "S7910080" 
                        },
                        {
                            "sku": "S2212819" 
                        },
                        {
                            "sku": "S7812959" 
                        },
                        {
                            "sku": "S6502878" 
                        },
                        {
                            "sku": "S7910091" 
                        },
                        {
                            "sku": "S7914583" 
                        },
                        {
                            "sku": "S2212287" 
                        },
                        {
                            "sku": "S7906943" 
                        },
                        {
                            "sku": "V1706799" 
                        },
                        {
                            "sku": "S7909347" 
                        },
                        {
                            "sku": "S3400316" 
                        },
                        {
                            "sku": "S7913213" 
                        },
                        {
                            "sku": "S7107908" 
                        },
                        {
                            "sku": "V1706798" 
                        },
                        {
                            "sku": "S7916533" 
                        },
                        {
                            "sku": "S7910097" 
                        },
                        {
                            "sku": "S7911903" 
                        },
                        {
                            "sku": "S7906683" 
                        },
                        {
                            "sku": "S7908029" 
                        },
                        {
                            "sku": "S7904567" 
                        },
                        {
                            "sku": "S7901477" 
                        },
                        {
                            "sku": "S7901523" 
                        },
                        {
                            "sku": "S7907518" 
                        },
                        {
                            "sku": "S7905214" 
                        },
                        {
                            "sku": "S7903838" 
                        },
                        {
                            "sku": "V1705104" 
                        },
                        {
                            "sku": "S7904807" 
                        },
                        {
                            "sku": "S7910094" 
                        },
                        {
                            "sku": "S7901215" 
                        },
                        {
                            "sku": "S7915107" 
                        },
                        {
                            "sku": "S7917884" 
                        },
                        {
                            "sku": "S7911776" 
                        },
                        {
                            "sku": "S2205903" 
                        },
                        {
                            "sku": "S7106378" 
                        },
                        {
                            "sku": "S7905591" 
                        },
                        {
                            "sku": "S6501209" 
                        },
                        {
                            "sku": "S7903578" 
                        },
                        {
                            "sku": "S7918410" 
                        },
                        {
                            "sku": "S7908241" 
                        },
                        {
                            "sku": "S7106390" 
                        },
                        {
                            "sku": "S6503818" 
                        },
                        {
                            "sku": "V1706806" 
                        },
                        {
                            "sku": "S7905253" 
                        },
                        {
                            "sku": "S2213123" 
                        },
                        {
                            "sku": "S7905589" 
                        },
                        {
                            "sku": "S7918377" 
                        },
                        {
                            "sku": "S7152247" 
                        },
                        {
                            "sku": "S7915210" 
                        },
                        {
                            "sku": "S7910731" 
                        },
                        {
                            "sku": "S7919962" 
                        },
                        {
                            "sku": "S6500593" 
                        },
                        {
                            "sku": "S7900899" 
                        },
                        {
                            "sku": "S6500419" 
                        },
                        {
                            "sku": "S7903346" 
                        },
                        {
                            "sku": "S6500915" 
                        },
                        {
                            "sku": "S7903187" 
                        },
                        {
                            "sku": "S7914089" 
                        },
                        {
                            "sku": "S7919963" 
                        },
                        {
                            "sku": "S7903276" 
                        },
                        {
                            "sku": "V1706797" 
                        },
                        {
                            "sku": "S7919059" 
                        },
                        {
                            "sku": "S6502986" 
                        },
                        {
                            "sku": "S7915105" 
                        },
                        {
                            "sku": "S7902437" 
                        },
                        {
                            "sku": "S7901075" 
                        },
                        {
                            "sku": "S6501204" 
                        },
                        {
                            "sku": "S6500843" 
                        },
                        {
                            "sku": "S7906748" 
                        },
                        {
                            "sku": "S7915295" 
                        },
                        {
                            "sku": "S2205908" 
                        },
                        {
                            "sku": "S7911902" 
                        },
                        {
                            "sku": "S6500244" 
                        },
                        {
                            "sku": "S7917735" 
                        },
                        {
                            "sku": "S6500847" 
                        },
                        {
                            "sku": "S7900314" 
                        },
                        {
                            "sku": "S7903568" 
                        },
                        {
                            "sku": "S37112510" 
                        },
                        {
                            "sku": "V0103048" 
                        },
                        {
                            "sku": "S7912305" 
                        },
                        {
                            "sku": "S0407579" 
                        },
                        {
                            "sku": "S6500328" 
                        },
                        {
                            "sku": "S6501580" 
                        },
                        {
                            "sku": "S7901807" 
                        },
                        {
                            "sku": "S7909625" 
                        },
                        {
                            "sku": "S7911845" 
                        },
                        {
                            "sku": "S6500984" 
                        },
                        {
                            "sku": "S6500972" 
                        },
                        {
                            "sku": "S7917764" 
                        },
                        {
                            "sku": "S7914511" 
                        },
                        {
                            "sku": "S6501016" 
                        },
                        {
                            "sku": "S7913479" 
                        },
                        {
                            "sku": "S7914173" 
                        },
                        {
                            "sku": "S6501007" 
                        },
                        {
                            "sku": "S6500395" 
                        },
                        {
                            "sku": "S7920338" 
                        },
                        {
                            "sku": "S7915212" 
                        },
                        {
                            "sku": "S7910796" 
                        },
                        {
                            "sku": "S2200351" 
                        },
                        {
                            "sku": "V0103218" 
                        },
                        {
                            "sku": "S7908032" 
                        },
                        {
                            "sku": "S7910090" 
                        },
                        {
                            "sku": "S7917276" 
                        },
                        {
                            "sku": "S6503819" 
                        },
                        {
                            "sku": "S7915106" 
                        },
                        {
                            "sku": "S7914527" 
                        },
                        {
                            "sku": "S7901119" 
                        },
                        {
                            "sku": "S7902735" 
                        },
                        {
                            "sku": "S7913393" 
                        },
                        {
                            "sku": "V1706796" 
                        },
                        {
                            "sku": "S7901120" 
                        },
                        {
                            "sku": "S6500064" 
                        },
                        {
                            "sku": "S7913478" 
                        },
                        {
                            "sku": "S7904811" 
                        },
                        {
                            "sku": "S7913387" 
                        },
                        {
                            "sku": "S6503191" 
                        },
                        {
                            "sku": "S6500420" 
                        },
                        {
                            "sku": "S7907606" 
                        },
                        {
                            "sku": "S7916094" 
                        },
                        {
                            "sku": "S7106675" 
                        },
                        {
                            "sku": "S2212289" 
                        },
                        {
                            "sku": "S7920204" 
                        },
                        {
                            "sku": "S7909514" 
                        },
                        {
                            "sku": "S7916534" 
                        },
                        {
                            "sku": "S7106563" 
                        },
                        {
                            "sku": "S7905745" 
                        },
                        {
                            "sku": "S7912396" 
                        },
                        {
                            "sku": "S3727095" 
                        },
                        {
                            "sku": "S7913390" 
                        },
                        {
                            "sku": "S7906916" 
                        },
                        {
                            "sku": "S7917758" 
                        },
                        {
                            "sku": "S7902765" 
                        },
                        {
                            "sku": "S7917761" 
                        },
                        {
                            "sku": "S7903837" 
                        },
                        {
                            "sku": "S2205902" 
                        },
                        {
                            "sku": "S7920326" 
                        },
                        {
                            "sku": "S7919960" 
                        },
                        {
                            "sku": "S6501052" 
                        },
                        {
                            "sku": "S1906364" 
                        },
                        {
                            "sku": "S7901148" 
                        },
                        {
                            "sku": "S7910092" 
                        },
                        {
                            "sku": "S2212242" 
                        },
                        {
                            "sku": "S7914172" 
                        },
                        {
                            "sku": "S7913384" 
                        },
                        {
                            "sku": "S2205900" 
                        },
                        {
                            "sku": "S6500950" 
                        },
                        {
                            "sku": "S7904342" 
                        },
                        {
                            "sku": "S7137858" 
                        },
                        {
                            "sku": "S7919000" 
                        },
                        {
                            "sku": "S7903520" 
                        },
                        {
                            "sku": "S7917279" 
                        },
                        {
                            "sku": "S1903211" 
                        },
                        {
                            "sku": "S7908740" 
                        },
                        {
                            "sku": "S7904812" 
                        },
                        {
                            "sku": "S2201504" 
                        },
                        {
                            "sku": "S7902435" 
                        },
                        {
                            "sku": "S1903396" 
                        },
                        {
                            "sku": "S7901216" 
                        },
                        {
                            "sku": "S7903274" 
                        },
                        {
                            "sku": "S6503008" 
                        },
                        {
                            "sku": "S7910089" 
                        },
                        {
                            "sku": "S7903572" 
                        },
                        {
                            "sku": "S7917808" 
                        },
                        {
                            "sku": "S7912368" 
                        },
                        {
                            "sku": "S7910100" 
                        },
                        {
                            "sku": "S2212288" 
                        },
                        {
                            "sku": "S7904447" 
                        },
                        {
                            "sku": "S6503197" 
                        },
                        {
                            "sku": "S7106425" 
                        },
                        {
                            "sku": "S7914090" 
                        },
                        {
                            "sku": "S7911777" 
                        },
                        {
                            "sku": "S7906461" 
                        },
                        {
                            "sku": "V1706803" 
                        },
                        {
                            "sku": "S7152171" 
                        },
                        {
                            "sku": "S7906858" 
                        },
                        {
                            "sku": "S7917277" 
                        },
                        {
                            "sku": "S6500577" 
                        },
                        {
                            "sku": "V1706792" 
                        },
                        {
                            "sku": "S7902439" 
                        },
                        {
                            "sku": "S6500912" 
                        },
                        {
                            "sku": "S7901149" 
                        },
                        {
                            "sku": "S6500260" 
                        },
                        {
                            "sku": "S2212286" 
                        },
                        {
                            "sku": "S7919959" 
                        },
                        {
                            "sku": "S6500414" 
                        },
                        {
                            "sku": "S7169966" 
                        },
                        {
                            "sku": "S7910099" 
                        },
                        {
                            "sku": "S7901929" 
                        },
                        {
                            "sku": "S7920203" 
                        },
                        {
                            "sku": "S7917411" 
                        },
                        {
                            "sku": "S37113843" 
                        },
                        {
                            "sku": "S7902082" 
                        },
                        {
                            "sku": "S7914763" 
                        },
                        {
                            "sku": "S2210214" 
                        },
                        {
                            "sku": "S7185065" 
                        },
                        {
                            "sku": "S7905967" 
                        },
                        {
                            "sku": "S7901930" 
                        },
                        {
                            "sku": "S7902764" 
                        },
                        {
                            "sku": "S7910078" 
                        },
                        {
                            "sku": "S6500072" 
                        },
                        {
                            "sku": "V1706815" 
                        },
                        {
                            "sku": "S7906463" 
                        },
                        {
                            "sku": "S7910085" 
                        },
                        {
                            "sku": "S7904569" 
                        },
                        {
                            "sku": "S7914174" 
                        },
                        {
                            "sku": "S7913201" 
                        },
                        {
                            "sku": "S6500708" 
                        },
                        {
                            "sku": "S7918375" 
                        },
                        {
                            "sku": "S7917048" 
                        },
                        {
                            "sku": "S6500474" 
                        },
                        {
                            "sku": "S7903519" 
                        },
                        {
                            "sku": "S6500835" 
                        },
                        {
                            "sku": "S6501082" 
                        },
                        {
                            "sku": "S7906462" 
                        },
                        {
                            "sku": "S7920052" 
                        },
                        {
                            "sku": "S7914942" 
                        },
                        {
                            "sku": "S7915771" 
                        },
                        {
                            "sku": "S7902081" 
                        },
                        {
                            "sku": "S3622613" 
                        },
                        {
                            "sku": "S7904314" 
                        },
                        {
                            "sku": "S6503192" 
                        },
                        {
                            "sku": "S6500913" 
                        },
                        {
                            "sku": "S7917140" 
                        },
                        {
                            "sku": "S7901922" 
                        },
                        {
                            "sku": "S7906458" 
                        },
                        {
                            "sku": "S7910084" 
                        },
                        {
                            "sku": "S2200350" 
                        },
                        {
                            "sku": "S6500578" 
                        },
                        {
                            "sku": "S7913024" 
                        },
                        {
                            "sku": "S7920082" 
                        },
                        {
                            "sku": "S0408198" 
                        },
                        {
                            "sku": "S0448776" 
                        },
                        {
                            "sku": "S7900635" 
                        },
                        {
                            "sku": "S7901463" 
                        },
                        {
                            "sku": "V1705167" 
                        },
                        {
                            "sku": "S7917032" 
                        },
                        {
                            "sku": "V1705095" 
                        },
                        {
                            "sku": "S6502949" 
                        },
                        {
                            "sku": "S7606570" 
                        },
                        {
                            "sku": "S0441877" 
                        },
                        {
                            "sku": "S7187634" 
                        },
                        {
                            "sku": "V1700409" 
                        },
                        {
                            "sku": "S6502235" 
                        },
                        {
                            "sku": "S6502196" 
                        },
                        {
                            "sku": "S6502248" 
                        },
                        {
                            "sku": "S2201298" 
                        },
                        {
                            "sku": "S7715687" 
                        },
                        {
                            "sku": "S7815421" 
                        },
                        {
                            "sku": "S7902320" 
                        },
                        {
                            "sku": "S0429845" 
                        },
                        {
                            "sku": "S7604225" 
                        },
                        {
                            "sku": "V1701696" 
                        },
                        {
                            "sku": "S7784812" 
                        },
                        {
                            "sku": "S2703695" 
                        },
                        {
                            "sku": "S0448065" 
                        },
                        {
                            "sku": "S7600970" 
                        },
                        {
                            "sku": "S0442238" 
                        },
                        {
                            "sku": "S7604619" 
                        },
                        {
                            "sku": "V1704484" 
                        },
                        {
                            "sku": "S6502247" 
                        },
                        {
                            "sku": "S7914887" 
                        },
                        {
                            "sku": "V1705172" 
                        },
                        {
                            "sku": "S7605985" 
                        },
                        {
                            "sku": "S6502251" 
                        },
                        {
                            "sku": "S6503312" 
                        },
                        {
                            "sku": "S4700186" 
                        },
                        {
                            "sku": "S7784790" 
                        },
                        {
                            "sku": "V1705295" 
                        },
                        {
                            "sku": "S0441930" 
                        },
                        {
                            "sku": "S0436914" 
                        },
                        {
                            "sku": "S0442552" 
                        },
                        {
                            "sku": "S4700210" 
                        },
                        {
                            "sku": "S6502202" 
                        },
                        {
                            "sku": "S7900539" 
                        },
                        {
                            "sku": "S6502201" 
                        },
                        {
                            "sku": "S2211864" 
                        },
                        {
                            "sku": "S3408286" 
                        },
                        {
                            "sku": "S6502199" 
                        },
                        {
                            "sku": "S4700258" 
                        },
                        {
                            "sku": "S7800111" 
                        },
                        {
                            "sku": "S4700185" 
                        },
                        {
                            "sku": "S7715696" 
                        },
                        {
                            "sku": "V1705170" 
                        },
                        {
                            "sku": "V1701772" 
                        },
                        {
                            "sku": "S7910836" 
                        },
                        {
                            "sku": "S4700228" 
                        },
                        {
                            "sku": "S7910839" 
                        },
                        {
                            "sku": "V1704338" 
                        },
                        {
                            "sku": "V1705192" 
                        },
                        {
                            "sku": "S6503673" 
                        },
                        {
                            "sku": "S4700025" 
                        },
                        {
                            "sku": "S2201294" 
                        },
                        {
                            "sku": "S7907343" 
                        },
                        {
                            "sku": "V1705283" 
                        },
                        {
                            "sku": "V1705267" 
                        },
                        {
                            "sku": "S4700040" 
                        },
                        {
                            "sku": "S0408306" 
                        },
                        {
                            "sku": "S0409523" 
                        },
                        {
                            "sku": "S7153835" 
                        },
                        {
                            "sku": "S0430615" 
                        },
                        {
                            "sku": "S4700030" 
                        },
                        {
                            "sku": "V1705174" 
                        },
                        {
                            "sku": "S0441398" 
                        },
                        {
                            "sku": "S7600541" 
                        },
                        {
                            "sku": "S5622717" 
                        },
                        {
                            "sku": "V1700412" 
                        },
                        {
                            "sku": "S7905363" 
                        },
                        {
                            "sku": "S7784795" 
                        },
                        {
                            "sku": "V1705166" 
                        },
                        {
                            "sku": "S2203692" 
                        },
                        {
                            "sku": "S7178961" 
                        },
                        {
                            "sku": "S6502222" 
                        },
                        {
                            "sku": "S7919770" 
                        },
                        {
                            "sku": "S4700067" 
                        },
                        {
                            "sku": "S7715690" 
                        },
                        {
                            "sku": "V1700399" 
                        },
                        {
                            "sku": "S7907103" 
                        },
                        {
                            "sku": "S7170766" 
                        },
                        {
                            "sku": "S4700198" 
                        },
                        {
                            "sku": "S0448514" 
                        },
                        {
                            "sku": "S6503672" 
                        },
                        {
                            "sku": "S4700170" 
                        },
                        {
                            "sku": "V1704695" 
                        },
                        {
                            "sku": "S6502249" 
                        },
                        {
                            "sku": "S7149772" 
                        },
                        {
                            "sku": "S7914897" 
                        },
                        {
                            "sku": "S7901633" 
                        },
                        {
                            "sku": "S0402724" 
                        },
                        {
                            "sku": "S7784811" 
                        },
                        {
                            "sku": "V1704391" 
                        },
                        {
                            "sku": "V1705381" 
                        },
                        {
                            "sku": "V1708045" 
                        },
                        {
                            "sku": "S7602647" 
                        },
                        {
                            "sku": "V1706719" 
                        },
                        {
                            "sku": "S4700256" 
                        },
                        {
                            "sku": "S7603925" 
                        },
                        {
                            "sku": "S8102662" 
                        },
                        {
                            "sku": "S7600635" 
                        },
                        {
                            "sku": "S0449452" 
                        },
                        {
                            "sku": "S7900634" 
                        },
                        {
                            "sku": "S7905364" 
                        },
                        {
                            "sku": "S7914888" 
                        },
                        {
                            "sku": "V1706842" 
                        },
                        {
                            "sku": "V1705481" 
                        },
                        {
                            "sku": "S7149775" 
                        },
                        {
                            "sku": "S6502232" 
                        },
                        {
                            "sku": "S7907340" 
                        },
                        {
                            "sku": "S2211859" 
                        },
                        {
                            "sku": "S4700233" 
                        },
                        {
                            "sku": "V1705477" 
                        },
                        {
                            "sku": "S7715907" 
                        },
                        {
                            "sku": "S6503160" 
                        },
                        {
                            "sku": "V1704852" 
                        },
                        {
                            "sku": "S7113857" 
                        },
                        {
                            "sku": "V1700105" 
                        },
                        {
                            "sku": "S6502068" 
                        },
                        {
                            "sku": "S7784786" 
                        },
                        {
                            "sku": "S7715699" 
                        },
                        {
                            "sku": "S6503004" 
                        },
                        {
                            "sku": "S4700221" 
                        },
                        {
                            "sku": "S7113483" 
                        },
                        {
                            "sku": "S0420815" 
                        },
                        {
                            "sku": "S7911930" 
                        },
                        {
                            "sku": "S7165127" 
                        },
                        {
                            "sku": "V1705207" 
                        },
                        {
                            "sku": "S0442637" 
                        },
                        {
                            "sku": "S4700232" 
                        },
                        {
                            "sku": "S4700123" 
                        },
                        {
                            "sku": "S4700124" 
                        },
                        {
                            "sku": "S2706197" 
                        },
                        {
                            "sku": "S7604709" 
                        },
                        {
                            "sku": "S4700158" 
                        },
                        {
                            "sku": "S0439177" 
                        },
                        {
                            "sku": "S8100086" 
                        },
                        {
                            "sku": "S7902321" 
                        },
                        {
                            "sku": "S6502256" 
                        },
                        {
                            "sku": "S7113730" 
                        },
                        {
                            "sku": "V1707903" 
                        },
                        {
                            "sku": "V1707907" 
                        },
                        {
                            "sku": "S7169955" 
                        },
                        {
                            "sku": "S7113985" 
                        },
                        {
                            "sku": "S6503650" 
                        },
                        {
                            "sku": "S0438708" 
                        },
                        {
                            "sku": "S0422343" 
                        },
                        {
                            "sku": "V1706721" 
                        },
                        {
                            "sku": "V1704668" 
                        },
                        {
                            "sku": "V1704781" 
                        },
                        {
                            "sku": "S7112969" 
                        },
                        {
                            "sku": "S7113157" 
                        },
                        {
                            "sku": "S7784815" 
                        },
                        {
                            "sku": "S4700272" 
                        },
                        {
                            "sku": "S7113979" 
                        },
                        {
                            "sku": "V1707892" 
                        },
                        {
                            "sku": "V1705296" 
                        },
                        {
                            "sku": "S4700053" 
                        },
                        {
                            "sku": "V1706705" 
                        },
                        {
                            "sku": "S4700048" 
                        },
                        {
                            "sku": "S0404710" 
                        },
                        {
                            "sku": "V1700410" 
                        },
                        {
                            "sku": "S0448063" 
                        },
                        {
                            "sku": "S7912204" 
                        },
                        {
                            "sku": "S2210955" 
                        },
                        {
                            "sku": "S0443444" 
                        },
                        {
                            "sku": "S7165119" 
                        },
                        {
                            "sku": "S2203859" 
                        },
                        {
                            "sku": "V1704681" 
                        },
                        {
                            "sku": "V1705088" 
                        },
                        {
                            "sku": "S7715677" 
                        },
                        {
                            "sku": "S6502129" 
                        },
                        {
                            "sku": "S7912998" 
                        },
                        {
                            "sku": "S3608549" 
                        },
                        {
                            "sku": "S7181013" 
                        },
                        {
                            "sku": "V1706838" 
                        },
                        {
                            "sku": "S7603934" 
                        },
                        {
                            "sku": "S0416850" 
                        },
                        {
                            "sku": "S7913271" 
                        },
                        {
                            "sku": "V1705065" 
                        },
                        {
                            "sku": "S4700275" 
                        },
                        {
                            "sku": "S6502253" 
                        },
                        {
                            "sku": "V1706734" 
                        },
                        {
                            "sku": "S7901376" 
                        },
                        {
                            "sku": "S8100104" 
                        },
                        {
                            "sku": "S4700174" 
                        },
                        {
                            "sku": "S4700177" 
                        },
                        {
                            "sku": "V0103525" 
                        },
                        {
                            "sku": "S4700034" 
                        },
                        {
                            "sku": "S2706200" 
                        },
                        {
                            "sku": "S0407694" 
                        },
                        {
                            "sku": "S2702037" 
                        },
                        {
                            "sku": "V1708007" 
                        },
                        {
                            "sku": "S2702065" 
                        },
                        {
                            "sku": "S7606388" 
                        },
                        {
                            "sku": "S8101094" 
                        },
                        {
                            "sku": "V1700425" 
                        },
                        {
                            "sku": "S0427354" 
                        },
                        {
                            "sku": "S7914900" 
                        },
                        {
                            "sku": "S0442595" 
                        },
                        {
                            "sku": "S6502257" 
                        },
                        {
                            "sku": "S7910838" 
                        },
                        {
                            "sku": "V0100780" 
                        },
                        {
                            "sku": "S2212785" 
                        },
                        {
                            "sku": "S7606028" 
                        },
                        {
                            "sku": "S7113921" 
                        },
                        {
                            "sku": "S0450486" 
                        },
                        {
                            "sku": "S7715681" 
                        },
                        {
                            "sku": "S0442216" 
                        },
                        {
                            "sku": "V0103716" 
                        },
                        {
                            "sku": "S7715680" 
                        },
                        {
                            "sku": "V1705223" 
                        },
                        {
                            "sku": "V1704392" 
                        },
                        {
                            "sku": "S0422529" 
                        },
                        {
                            "sku": "S7180139" 
                        },
                        {
                            "sku": "V1705066" 
                        },
                        {
                            "sku": "S7112918" 
                        },
                        {
                            "sku": "S7600418" 
                        },
                        {
                            "sku": "S7112899" 
                        },
                        {
                            "sku": "S6503311" 
                        },
                        {
                            "sku": "S4700156" 
                        },
                        {
                            "sku": "S0440990" 
                        },
                        {
                            "sku": "S0449446" 
                        },
                        {
                            "sku": "S6502246" 
                        },
                        {
                            "sku": "V1705083" 
                        },
                        {
                            "sku": "S2212244" 
                        },
                        {
                            "sku": "S6502198" 
                        },
                        {
                            "sku": "V1705054" 
                        },
                        {
                            "sku": "S2210973" 
                        },
                        {
                            "sku": "S4700137" 
                        },
                        {
                            "sku": "S7781512" 
                        },
                        {
                            "sku": "V1705168" 
                        },
                        {
                            "sku": "S0438262" 
                        },
                        {
                            "sku": "S7602575" 
                        },
                        {
                            "sku": "S7153573" 
                        },
                        {
                            "sku": "S7113749" 
                        },
                        {
                            "sku": "S2703187" 
                        },
                        {
                            "sku": "S2702039" 
                        },
                        {
                            "sku": "S0411993" 
                        },
                        {
                            "sku": "S0401032" 
                        },
                        {
                            "sku": "S7149766" 
                        },
                        {
                            "sku": "V1706840" 
                        },
                        {
                            "sku": "S6503250" 
                        },
                        {
                            "sku": "S7900540" 
                        },
                        {
                            "sku": "S2205866" 
                        },
                        {
                            "sku": "S5002478" 
                        },
                        {
                            "sku": "S8100084" 
                        },
                        {
                            "sku": "S7153548" 
                        },
                        {
                            "sku": "S0424125" 
                        },
                        {
                            "sku": "S0407934" 
                        },
                        {
                            "sku": "S0415398" 
                        },
                        {
                            "sku": "S7113594" 
                        },
                        {
                            "sku": "S7914901" 
                        },
                        {
                            "sku": "S0420988" 
                        },
                        {
                            "sku": "S7910837" 
                        },
                        {
                            "sku": "V1706831" 
                        },
                        {
                            "sku": "S2201328" 
                        },
                        {
                            "sku": "S7714724" 
                        },
                        {
                            "sku": "S4700114" 
                        },
                        {
                            "sku": "V1707723" 
                        },
                        {
                            "sku": "S2212374" 
                        },
                        {
                            "sku": "S7603963" 
                        },
                        {
                            "sku": "S7904176" 
                        },
                        {
                            "sku": "S0430616" 
                        },
                        {
                            "sku": "S4700088" 
                        },
                        {
                            "sku": "S7113922" 
                        },
                        {
                            "sku": "S7149787" 
                        },
                        {
                            "sku": "V0103462" 
                        },
                        {
                            "sku": "S2703694" 
                        },
                        {
                            "sku": "S7784792" 
                        },
                        {
                            "sku": "S7153565" 
                        },
                        {
                            "sku": "S2212450" 
                        },
                        {
                            "sku": "S2705605" 
                        },
                        {
                            "sku": "S2201332" 
                        },
                        {
                            "sku": "S7715683" 
                        },
                        {
                            "sku": "S0450003" 
                        },
                        {
                            "sku": "V1705264" 
                        },
                        {
                            "sku": "S6502813" 
                        },
                        {
                            "sku": "S6502238" 
                        },
                        {
                            "sku": "V1704340" 
                        },
                        {
                            "sku": "S2211998" 
                        },
                        {
                            "sku": "V0103610" 
                        },
                        {
                            "sku": "S2211865" 
                        },
                        {
                            "sku": "S0449882" 
                        },
                        {
                            "sku": "S0442896" 
                        },
                        {
                            "sku": "S0443050" 
                        },
                        {
                            "sku": "S2203694" 
                        },
                        {
                            "sku": "S0401051" 
                        },
                        {
                            "sku": "S8103015" 
                        },
                        {
                            "sku": "S6503620" 
                        },
                        {
                            "sku": "S0450766" 
                        },
                        {
                            "sku": "V0103242" 
                        },
                        {
                            "sku": "S0439912" 
                        },
                        {
                            "sku": "S7914886" 
                        },
                        {
                            "sku": "S6502250" 
                        },
                        {
                            "sku": "V1700428" 
                        },
                        {
                            "sku": "S0426127" 
                        },
                        {
                            "sku": "S7113159" 
                        },
                        {
                            "sku": "V1705280" 
                        },
                        {
                            "sku": "S0408304" 
                        },
                        {
                            "sku": "V1704493" 
                        },
                        {
                            "sku": "S2700029" 
                        },
                        {
                            "sku": "S2702041" 
                        },
                        {
                            "sku": "S7912189" 
                        },
                        {
                            "sku": "V1706722" 
                        },
                        {
                            "sku": "S7169967" 
                        },
                        {
                            "sku": "V1704775" 
                        },
                        {
                            "sku": "S0438098" 
                        },
                        {
                            "sku": "S7902331" 
                        },
                        {
                            "sku": "S7153961" 
                        },
                        {
                            "sku": "V1705086" 
                        },
                        {
                            "sku": "V1707985" 
                        },
                        {
                            "sku": "S0422916" 
                        },
                        {
                            "sku": "V1705297" 
                        },
                        {
                            "sku": "V1707898" 
                        },
                        {
                            "sku": "S4700211" 
                        },
                        {
                            "sku": "S0426999" 
                        },
                        {
                            "sku": "V1705025" 
                        },
                        {
                            "sku": "S7113032" 
                        },
                        {
                            "sku": "S7907104" 
                        },
                        {
                            "sku": "S6502233" 
                        },
                        {
                            "sku": "V1707896" 
                        },
                        {
                            "sku": "V1701635" 
                        },
                        {
                            "sku": "S6503251" 
                        },
                        {
                            "sku": "S7171429" 
                        },
                        {
                            "sku": "V1707904" 
                        },
                        {
                            "sku": "S3408284" 
                        },
                        {
                            "sku": "S7600906" 
                        },
                        {
                            "sku": "S0428223" 
                        },
                        {
                            "sku": "S0433904" 
                        },
                        {
                            "sku": "S7112917" 
                        },
                        {
                            "sku": "S4700033" 
                        },
                        {
                            "sku": "S4700172" 
                        },
                        {
                            "sku": "S8102669" 
                        },
                        {
                            "sku": "S2210952" 
                        },
                        {
                            "sku": "S0422253" 
                        },
                        {
                            "sku": "S7113650" 
                        },
                        {
                            "sku": "S6502245" 
                        },
                        {
                            "sku": "S0448066" 
                        },
                        {
                            "sku": "S7173657" 
                        },
                        {
                            "sku": "S4700195" 
                        },
                        {
                            "sku": "V1704337" 
                        },
                        {
                            "sku": "V1704495" 
                        },
                        {
                            "sku": "S2706196" 
                        },
                        {
                            "sku": "S7603964" 
                        },
                        {
                            "sku": "V1705482" 
                        },
                        {
                            "sku": "S7907105" 
                        },
                        {
                            "sku": "S8101087" 
                        },
                        {
                            "sku": "S7606489" 
                        },
                        {
                            "sku": "S0438710" 
                        },
                        {
                            "sku": "V1707901" 
                        },
                        {
                            "sku": "S8101089" 
                        },
                        {
                            "sku": "S2212763" 
                        },
                        {
                            "sku": "S7113977" 
                        },
                        {
                            "sku": "S4700239" 
                        },
                        {
                            "sku": "S7902317" 
                        },
                        {
                            "sku": "V1705098" 
                        },
                        {
                            "sku": "V1705089" 
                        },
                        {
                            "sku": "S2702042" 
                        },
                        {
                            "sku": "S0416851" 
                        },
                        {
                            "sku": "S4700182" 
                        },
                        {
                            "sku": "S0407697" 
                        },
                        {
                            "sku": "V1706717" 
                        },
                        {
                            "sku": "S7800112" 
                        },
                        {
                            "sku": "V1707905" 
                        },
                        {
                            "sku": "S7784804" 
                        },
                        {
                            "sku": "S7136060" 
                        },
                        {
                            "sku": "S6502203" 
                        },
                        {
                            "sku": "S7113649" 
                        },
                        {
                            "sku": "S4700227" 
                        },
                        {
                            "sku": "S0430129" 
                        },
                        {
                            "sku": "S2204615" 
                        },
                        {
                            "sku": "S6502059" 
                        },
                        {
                            "sku": "V1700398" 
                        },
                        {
                            "sku": "V1706716" 
                        },
                        {
                            "sku": "S2201354" 
                        },
                        {
                            "sku": "V1704670" 
                        },
                        {
                            "sku": "S6503288" 
                        },
                        {
                            "sku": "S7715911" 
                        },
                        {
                            "sku": "S7113942" 
                        },
                        {
                            "sku": "S7784793" 
                        },
                        {
                            "sku": "S6503249" 
                        },
                        {
                            "sku": "V1705221" 
                        },
                        {
                            "sku": "S4700118" 
                        },
                        {
                            "sku": "S2706199" 
                        },
                        {
                            "sku": "S2701936" 
                        },
                        {
                            "sku": "S7113163" 
                        },
                        {
                            "sku": "S2703696" 
                        },
                        {
                            "sku": "S0400242" 
                        },
                        {
                            "sku": "S4700117" 
                        },
                        {
                            "sku": "S2211403" 
                        },
                        {
                            "sku": "S0409555" 
                        },
                        {
                            "sku": "S4700135" 
                        },
                        {
                            "sku": "S7784799" 
                        },
                        {
                            "sku": "S2702036" 
                        },
                        {
                            "sku": "V1705294" 
                        },
                        {
                            "sku": "S7177268" 
                        },
                        {
                            "sku": "S6502236" 
                        },
                        {
                            "sku": "S7113931" 
                        },
                        {
                            "sku": "S2702066" 
                        },
                        {
                            "sku": "S5002279" 
                        },
                        {
                            "sku": "S7165125" 
                        },
                        {
                            "sku": "S7605982" 
                        },
                        {
                            "sku": "S7113520" 
                        },
                        {
                            "sku": "S7113509" 
                        },
                        {
                            "sku": "S7113970" 
                        },
                        {
                            "sku": "S2703693" 
                        },
                        {
                            "sku": "S7177267" 
                        },
                        {
                            "sku": "S7905922" 
                        },
                        {
                            "sku": "S7902330" 
                        },
                        {
                            "sku": "S2210954" 
                        },
                        {
                            "sku": "S7113862" 
                        },
                        {
                            "sku": "S7822204" 
                        },
                        {
                            "sku": "S0420515" 
                        },
                        {
                            "sku": "S8102340" 
                        },
                        {
                            "sku": "V1706737" 
                        },
                        {
                            "sku": "S7900550" 
                        },
                        {
                            "sku": "S4700281" 
                        },
                        {
                            "sku": "S0426201" 
                        },
                        {
                            "sku": "S0407313" 
                        },
                        {
                            "sku": "S4700173" 
                        },
                        {
                            "sku": "S2201355" 
                        },
                        {
                            "sku": "S7113969" 
                        },
                        {
                            "sku": "S6502226" 
                        },
                        {
                            "sku": "S7901539" 
                        },
                        {
                            "sku": "S4700224" 
                        },
                        {
                            "sku": "S7169178" 
                        },
                        {
                            "sku": "S2212243" 
                        },
                        {
                            "sku": "S0450135" 
                        },
                        {
                            "sku": "S4700259" 
                        },
                        {
                            "sku": "S4700136" 
                        },
                        {
                            "sku": "S0442215" 
                        },
                        {
                            "sku": "S4700280" 
                        },
                        {
                            "sku": "S7178545" 
                        },
                        {
                            "sku": "S2212746" 
                        },
                        {
                            "sku": "V1705305" 
                        },
                        {
                            "sku": "V0100594" 
                        },
                        {
                            "sku": "S8101090" 
                        },
                        {
                            "sku": "S7149788" 
                        },
                        {
                            "sku": "S2204613" 
                        },
                        {
                            "sku": "S2210536" 
                        },
                        {
                            "sku": "V1705023" 
                        },
                        {
                            "sku": "S7113980" 
                        },
                        {
                            "sku": "V1706712" 
                        },
                        {
                            "sku": "V1707900" 
                        },
                        {
                            "sku": "V1705080" 
                        },
                        {
                            "sku": "V1706695" 
                        },
                        {
                            "sku": "S2212039" 
                        },
                        {
                            "sku": "V1704776" 
                        },
                        {
                            "sku": "S2201300" 
                        },
                        {
                            "sku": "S7167398" 
                        },
                        {
                            "sku": "S0429134" 
                        },
                        {
                            "sku": "V1705196" 
                        },
                        {
                            "sku": "V1707895" 
                        },
                        {
                            "sku": "V1704696" 
                        },
                        {
                            "sku": "S6502234" 
                        },
                        {
                            "sku": "V1706844" 
                        },
                        {
                            "sku": "S0429844" 
                        },
                        {
                            "sku": "S7113929" 
                        },
                        {
                            "sku": "S7920285" 
                        },
                        {
                            "sku": "V1701629" 
                        },
                        {
                            "sku": "S4700066" 
                        },
                        {
                            "sku": "V1700108" 
                        },
                        {
                            "sku": "V1705171" 
                        },
                        {
                            "sku": "V1705214" 
                        },
                        {
                            "sku": "V0100515" 
                        },
                        {
                            "sku": "S7784789" 
                        },
                        {
                            "sku": "S0450488" 
                        },
                        {
                            "sku": "S4700068" 
                        },
                        {
                            "sku": "S0426200" 
                        },
                        {
                            "sku": "S7600363" 
                        },
                        {
                            "sku": "V1705024" 
                        },
                        {
                            "sku": "S6502200" 
                        },
                        {
                            "sku": "S6502197" 
                        },
                        {
                            "sku": "S6503252" 
                        },
                        {
                            "sku": "V1705220" 
                        },
                        {
                            "sku": "S7602832" 
                        },
                        {
                            "sku": "S7784806" 
                        },
                        {
                            "sku": "S6502221" 
                        },
                        {
                            "sku": "V1704497" 
                        },
                        {
                            "sku": "V1707902" 
                        },
                        {
                            "sku": "S7715682" 
                        },
                        {
                            "sku": "V1704589" 
                        },
                        {
                            "sku": "S2201299" 
                        },
                        {
                            "sku": "S7112397" 
                        },
                        {
                            "sku": "S4700273" 
                        },
                        {
                            "sku": "S7153951" 
                        },
                        {
                            "sku": "V1706720" 
                        },
                        {
                            "sku": "S8102666" 
                        },
                        {
                            "sku": "S2210780" 
                        },
                        {
                            "sku": "S7165122" 
                        },
                        {
                            "sku": "S0442930" 
                        },
                        {
                            "sku": "S4700115" 
                        },
                        {
                            "sku": "S7113519" 
                        },
                        {
                            "sku": "S7902316" 
                        },
                        {
                            "sku": "S7907348" 
                        },
                        {
                            "sku": "S2201309" 
                        },
                        {
                            "sku": "S4700024" 
                        },
                        {
                            "sku": "V0103346" 
                        },
                        {
                            "sku": "S7920284" 
                        },
                        {
                            "sku": "V1707912" 
                        },
                        {
                            "sku": "V1700411" 
                        },
                        {
                            "sku": "S0433098" 
                        },
                        {
                            "sku": "S6502223" 
                        },
                        {
                            "sku": "S0429581" 
                        },
                        {
                            "sku": "S0438008" 
                        },
                        {
                            "sku": "V1707906" 
                        },
                        {
                            "sku": "S0400933" 
                        },
                        {
                            "sku": "S7820251" 
                        },
                        {
                            "sku": "V1706713" 
                        },
                        {
                            "sku": "S7600173" 
                        },
                        {
                            "sku": "S4700045" 
                        },
                        {
                            "sku": "S2700669" 
                        },
                        {
                            "sku": "S0423786" 
                        },
                        {
                            "sku": "V1707726" 
                        },
                        {
                            "sku": "S7113978" 
                        },
                        {
                            "sku": "V1704844" 
                        },
                        {
                            "sku": "S7153822" 
                        },
                        {
                            "sku": "S0438646" 
                        },
                        {
                            "sku": "S7821538" 
                        },
                        {
                            "sku": "S4700023" 
                        },
                        {
                            "sku": "V1705072" 
                        },
                        {
                            "sku": "S2210781" 
                        },
                        {
                            "sku": "S4700058" 
                        },
                        {
                            "sku": "S7153977" 
                        },
                        {
                            "sku": "S2201333" 
                        },
                        {
                            "sku": "S7824552" 
                        },
                        {
                            "sku": "S4700212" 
                        },
                        {
                            "sku": "S6503315" 
                        },
                        {
                            "sku": "S7172648" 
                        },
                        {
                            "sku": "S4700116" 
                        },
                        {
                            "sku": "S8100103" 
                        },
                        {
                            "sku": "S2211057" 
                        },
                        {
                            "sku": "V1708040" 
                        },
                        {
                            "sku": "V1706706" 
                        }
                        ] 
                        } 
            }';
        // En-têtes de la requête
        $headers = [
            'Authorization: Bearer ' . BigBuyTvs::API_KEY_PROD,
            'Content-Type: application/json'
        ];

        // Configuration de la requête
        $options = [
            'http' => [
                'header' => $headers,
                'method' => 'POST',
                'content' => $data,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        // Création du contexte de la requête
        $context = stream_context_create($options);

        // Envoi de la requête et récupération de la réponse
        $response = file_get_contents($url, false, $context);

        // Vérification de la réponse
        if ($response === false) {
            // Erreur lors de la requête
            echo "Erreur lors de l'envoi de la requête API.";
        } else {
            $response_data = json_decode($response, true);
            //var_export($response_data);
            echo "terminé.";
            return $response_data;
        }
    }
}
