<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Description:    Spanish language file
 *
 * Authors:        Liran Tal <liran@enginx.com>
 *                 Irving Bermudez <bigchirv@gmail.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/lang/es_VE.php') !== false) {
    header("Location: ../index.php");
    exit;
}

$l['all']['daloRADIUS'] = sprintf("daloRADIUS %s", $configValues['DALORADIUS_VERSION']);
$l['all']['daloRADIUSVersion'] = sprintf("version %s ", $configValues['DALORADIUS_VERSION']);
$l['all']['copyright1'] = 'Administraci&oacute;n RADIUS, Reportes, Conteo y Facturaci&oacute;n desarrollado por '
                        . '<a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>';
$l['all']['copyright2'] = 'daloRADIUS - Copyright &copy; 2007-' . date('Y') . ' by <a target="_blank" href="https://github.com/lirantal/daloradius">Liran Tal</a>.<br>'
                        . 'daloRADIUS has been enhanced by <a target="_blank" href="https://github.com/filippolauria">Filippo Lauria</a>.';

$l['all']['ID'] = "ID";
$l['all']['PoolName'] = "Pool Name";
$l['all']['CalledStationId'] = "CalledStationId";
$l['all']['CallingStationID'] = "CallingStationID";
$l['all']['ExpiryTime'] = "Tiempo de expiraci&oacute;n";
$l['all']['PoolKey'] = "Pool Key";

/********************************************************************************/
/* Vendor Attributes related translation                                        */
/********************************************************************************/
$l['all']['Dictionary'] = "Diccionario";
$l['all']['VendorID'] = "Id del vendedor";
$l['all']['VendorName'] = "Nombre del vendedor";
$l['all']['VendorAttribute'] = "Atributo del vendedor";
$l['all']['RecommendedOP'] = "OP recomendado";
$l['all']['RecommendedTable'] = "Tabla recomendada";
$l['all']['RecommendedTooltip'] = "Sugerencia recomendada";
$l['all']['RecommendedHelper'] = "Ayuda recomendada";
/********************************************************************************/

/* radius related text */
$l['all']['RADIUSDictionaryPath'] = "Ruta a los diccionarios RADIUS";


$l['all']['Compare'] = "Comparar";

$l['all']['Section'] = "Secci&oacute;n";
$l['all']['Item'] = "Item";

$l['all']['RemoveRadacctRecords'] = "Remover los registros de informaci&oacute;n";

$l['all']['CleanupSessions'] = "Limpiar las sesiones anteriores a";
$l['all']['DeleteSessions'] = "Eliminar las sesiones anteriores a";

$l['all']['StartingDate'] = "Fecha Inicial";
$l['all']['EndingDate'] = "Fecha Final";

$l['all']['Realm'] = "Reino";
$l['all']['RealmName'] = "Nombre del Reino";
$l['all']['RealmSecret'] = "Secreto del Reino";
$l['all']['AuthHost'] = "Host de Autorizaci&oacute;n";
$l['all']['AcctHost'] = "Host de Contabilizaci&oacute;n";
$l['all']['Ldflag'] = "ldflag";
$l['all']['Nostrip'] = "nostrip";
$l['all']['Notrealm'] = "notrealm";
$l['all']['Hints'] = "hints";

$l['all']['Proxy'] = "Proxy";
$l['all']['ProxyName'] = "Nombre del Proxy";
$l['all']['ProxySecret'] = "Secreto del Proxy";
$l['all']['DeadTime'] = "Dead Time";
$l['all']['RetryDelay'] = "Espera entre intentos";
$l['all']['RetryCount'] = "N&uacute;mero de reintentos";
$l['all']['DefaultFallback'] = "Servidor de respaldo";

$l['all']['SimultaneousUse'] = "Uso-Simult&aacute;neo";
$l['all']['NasID'] = "ID del NAS";
$l['all']['Nas'] = "NAS ";
$l['all']['NasIPHost'] = "NAS IP/Host";
$l['all']['NasShortname'] = "Noombre corto del NAS";
$l['all']['NasType'] = "Tipo de NAS";
$l['all']['NasPorts'] = "Puertos del NAS";
$l['all']['NasSecret'] = "Secreto del NAS";
$l['all']['NasVirtualServer'] = "NAS Virtual Server";
$l['all']['NasCommunity'] = "Comunidad del NAS";
$l['all']['NasDescription'] = "Descripci&oacute;n del NAS";
$l['all']['PacketType'] = "Tipo de Paquete";
$l['all']['HotSpot'] = "Hotspot";
$l['all']['HotSpots'] = "Hotspots";
$l['all']['HotSpotName'] = "Nombre del hotspot";
$l['all']['Name'] = "Nombre";
$l['all']['Username'] = "Usuario";
$l['all']['Password'] = "Contrase&ntilde;a";
$l['all']['PasswordType'] = "Tipo de contrase&ntilde;a";
$l['all']['IPAddress'] = "Direcci&oacute;n IP";
$l['all']['Profile'] = "Perfil";
$l['all']['Group'] = "Grupo";
$l['all']['Groupname'] = "Nombre del grupo";
$l['all']['ProfilePriority'] = "Prioridad del Perfil";
$l['all']['GroupPriority'] = "Prioridad del Grupo";
$l['all']['CurrentGroupname'] = "Nombre actual del grupo";
$l['all']['NewGroupname'] = "Nuevo nombre del grupo";
$l['all']['Priority'] = "Prioridad";
$l['all']['Attribute'] = "Atributo";
$l['all']['Operator'] = "Operador";
$l['all']['Value'] = "Valor";
$l['all']['NewValue'] = "Nuevo valor";
$l['all']['MaxTimeExpiration'] = "Tiempo m&aacute;ximo / Expiraci&oacute;n";
$l['all']['UsedTime'] = "Tiempo utilizado";
$l['all']['Status'] = "Estado";
$l['all']['Usage'] = "Uso";
$l['all']['StartTime'] = "Hora de inicio";
$l['all']['StopTime'] = "Hora de finalizaci&oacute;n";
$l['all']['TotalTime'] = "Tiempo total";
$l['all']['Bytes'] = "Bytes";
$l['all']['Upload'] = "Subida";
$l['all']['Download'] = "Descarga";
$l['all']['Rollback'] = "Roll-back";
$l['all']['Termination'] = "Terminaci&oacute;n";
$l['all']['NASIPAddress'] = "Direcci&oacute;n IP del NAS";
$l['all']['NASShortName'] = "NAS Short Name";
$l['all']['Action'] = "Acci&oacute;n";
$l['all']['UniqueUsers'] = "Unique Users";
$l['all']['TotalHits'] = "Hits totales";
$l['all']['AverageTime'] = "Tiempo promedio";
$l['all']['Records'] = "Registros";
$l['all']['Summary'] = "Sumario";
$l['all']['Statistics'] = "Estad&iacute;sticas";
$l['all']['Credit'] = "Cr&eacute;ditos";
$l['all']['Used'] = "Usados";
$l['all']['LeftTime'] = "Tiempo restante";
$l['all']['LeftPercent'] = "% tiempo restante";
$l['all']['TotalSessions'] = "Total de sesiones";
$l['all']['LastLoginTime'] = "Ultima hora de acceso";
$l['all']['TotalSessionTime'] = "Tiempo total de la sesi&oacute;n";
$l['all']['RateName'] = "Nombre de la tarifa";
$l['all']['RateType'] = "Tipo de tarifa";
$l['all']['RateCost'] = "Costo de la tarifa";
$l['all']['Billed'] = "Cobrado";
$l['all']['TotalUsers'] = "Cantidad de usuarios";
$l['all']['TotalBilled'] = "Usuarios a quienes se les ha cobrado";
$l['all']['CardBank'] = "Banco emisor";
$l['all']['Type'] = "Tipo";
$l['all']['CardBank'] = "Banco emisor";
$l['all']['MACAddress'] = "Direcci&oacute;n MAC";
$l['all']['Geocode'] = "Geocode";
$l['all']['PINCode'] = "C&oacute;digo PIN";
$l['all']['CreationDate'] = "Fecha de creaci&oacute;n";
$l['all']['CreationBy'] = "Creado por";
$l['all']['UpdateDate'] = "Fecha de actualizaci&oacute;n";
$l['all']['UpdateBy'] = "Actualizado por";

$l['all']['Discount'] = "Descuento";
$l['all']['BillAmount'] = "Monto cobrado";
$l['all']['BillAction'] = "Acci&oacute;n de cobro";
$l['all']['BillPerformer'] = "Cobrador";
$l['all']['BillReason'] = "Raz&oacute;n de cobro";
$l['all']['Lead'] = "Lead";
$l['all']['Coupon'] = "Cup&oacute;n";
$l['all']['OrderTaker'] = "Orden tomada por";
$l['all']['BillStatus'] = "Estado de la cuenta";
$l['all']['LastBill'] = "Ultima factura";
$l['all']['NextBill'] = "Siguiente factura";
$l['all']['PostalInvoice'] = "Factura via postal";
$l['all']['FaxInvoice'] = "Factura via Fax";
$l['all']['EmailInvoice'] = "Factura via correo electr&oacute;nico";

$l['all']['edit'] = "Editar";
$l['all']['del'] = "Eliminar";
$l['all']['groupslist'] = "Listado de grupos";
$l['all']['TestUser'] = "Probar usuario";
$l['all']['Accounting'] = "Conteo";
$l['all']['RADIUSReply'] = "Respuesta del servidor RADIUS";

$l['all']['Disconnect'] = "Desconectar";

$l['all']['Debug'] = "Depurar";
$l['all']['Timeout'] = "Tiempo de espera";
$l['all']['Retries'] = "Reintentos";
$l['all']['Count'] = "Informaci&oacute;n";
$l['all']['Requests'] = "Peticiones";

$l['all']['DatabaseHostname'] = "Nombre del servidor de base de datos";
$l['all']['DatabaseUser'] = "Usuario de Base de datos";
$l['all']['DatabasePass'] = "Contrase&ntilde;a de base de datos";
$l['all']['DatabaseName'] = "Nombre de la base de datos";

$l['all']['PrimaryLanguage'] = "Lenguaje principal";

$l['all']['PagesLogging'] = "Registrar las p&aacute;ginas visitadas";
$l['all']['QueriesLogging'] = "Registrar de consultas (reportes y gr&aatilde;ficos)";
$l['all']['ActionsLogging'] = "Registrar las acciones (env&iacute;o de formularios)";
$l['all']['FilenameLogging'] = "Archivo de registro (ruta completa)";
$l['all']['LoggingDebugOnPages'] = "Registrar la informaci&oacute;n de depuraci&oacute;n de cada p&aacute;gina";
$l['all']['LoggingDebugInfo'] = "Registrar la informaci&oacute;n adicional de depuraci&oacute;n";

$l['all']['PasswordHidden'] = "Esconder contrase&ntilde;as (Se muestran asteriscos)";
$l['all']['TablesListing'] = "Filas/Registros por tabla mostrada en una p&aacute;gina";
$l['all']['TablesListingNum'] = "Enumerar las filas en las tablas";
$l['all']['AjaxAutoComplete'] = "Habilitar la autocompletaci&oacute;n con AJAX";

$l['all']['RadiusServer'] = "Servidor RADIUS";
$l['all']['RadiusPort'] = "Puerto RADIUS";

$l['all']['UsernamePrefix'] = "Prefijo de los usuarios";
$l['all']['NumberInstances'] = "N&uacute;mero de instancias a crear";
$l['all']['UsernameLength'] = "Largo del nombre del usuario";
$l['all']['PasswordLength'] = "Largo de la contrase&ntilde;a";

$l['all']['Expiration'] = "Expiraci&oacute;n";
$l['all']['MaxAllSession'] = "Max-All-Session";
$l['all']['SessionTimeout'] = "Plazo de expiraci&oacute;n de la sesi&oacute;n";
$l['all']['IdleTimeout'] = "Tiempo m&aacute;ximo de inactividad";

$l['all']['DBEngine'] = "Motor de Base de datos";
$l['all']['radcheck'] = "radcheck";
$l['all']['radreply'] = "radreply";
$l['all']['radgroupcheck'] = "radgroupcheck";
$l['all']['radgroupreply'] = "radgroupreply";
$l['all']['usergroup'] = "usergroup";
$l['all']['radacct'] = "radacct";
$l['all']['operators'] = "operators";
$l['all']['billingrates'] = "Tarifas de cobro";
$l['all']['hotspots'] = "hotspots";
$l['all']['nas'] = "nas";
$l['all']['radpostauth'] = "radpostauth";
$l['all']['radippool'] = "radippool";
$l['all']['userinfo'] = "userinfo";
$l['all']['dictionary'] = "dictionary";
$l['all']['realms'] = "realms";
$l['all']['proxys'] = "proxys";
$l['all']['billingpaypal'] = "Cobros por Paypal";
$l['all']['billingplans'] = "Planes de facturaci&oacute;n";
$l['all']['billinghistory'] = "Hist&oacute;rico de cobros";
$l['all']['billinginfo'] = "Informaci&oacute;n de facturaci&oacute;n del usuario";

$l['all']['Month'] = "Mes";

$l['all']['PaymentDate'] = "Fecha de pago";
$l['all']['PaymentStatus'] = "Estado del pago";
$l['all']['FirstName'] = "Nombre";
$l['all']['LastName'] = "Apellido";
$l['all']['PayerStatus'] = "Estado del pagador";
$l['all']['PaymentAddressStatus'] = "Estado de la direcci&oacute;n del pagador";
$l['all']['PayerEmail'] = "Correo del pagador";
$l['all']['TxnId'] = "Id de la transacci&oacute;n";
$l['all']['PlanTimeType'] = "Plan Time Type";
$l['all']['PlanTimeBank'] = "Plan Time Bank";
$l['all']['PlanTimeRefillCost'] = "Costo de recarga del plan";
$l['all']['PlanTrafficRefillCost'] = "Costo de recarga del plan";
$l['all']['PlanBandwidthUp'] = "Ancho de banda de subida del plan";
$l['all']['PlanBandwidthDown'] = "Ancho de banda de descarga del plan";
$l['all']['PlanTrafficTotal'] = "Total de tr&aacute;fico del plan";
$l['all']['PlanTrafficDown'] = "Tr&aacute;fico de descarga del plan";
$l['all']['PlanTrafficUp'] = "Tr&aacute;fico de sudifa del plan";
$l['all']['PlanRecurring'] = "Recurrencia del plan";
$l['all']['PlanRecurringPeriod'] = "Periodo de recurrencia del plan";
$l['all']['PlanCost'] = "Costo del plan";
$l['all']['PlanSetupCost'] = "Costo de afiliaci&oacute;n";
$l['all']['PlanTax'] = "Impuesto asociado";
$l['all']['PlanCurrency'] = "Moneda del plan";
$l['all']['PlanGroup'] = "Perfil del plan (Grupo)";
$l['all']['PlanType'] = "Tipo de plan";
$l['all']['PlanName'] = "Nombre del plan";
$l['all']['PlanId'] = "Id del plan";
$l['all']['Quantity'] = "Cantidad";
$l['all']['ReceiverEmail'] = "Email del receptor";
$l['all']['Business'] = "Empresa";
$l['all']['Tax'] = "Impuesto";
$l['all']['Cost'] = "Costo";
$l['all']['TransactionFee'] = "Costo de la transacci&oacute;n";
$l['all']['PaymentCurrency'] = "Moneda de pago";
$l['all']['AddressRecipient'] = "Direcci&oacute;n del receptor";
$l['all']['Street'] = "Calle";
$l['all']['Country'] = "Pa&iacute;s";
$l['all']['CountryCode'] = "C&oacute;digo del pa&iacute;s";
$l['all']['City'] = "Ciudad";
$l['all']['State'] = "Estado";
$l['all']['Zip'] = "C&oacute;digo postal";

$l['all']['BusinessName'] = "Nombre de la empresa";
$l['all']['BusinessPhone'] = "Tel&eacute;fono de la empresa";
$l['all']['BusinessAddress'] = "Direcci&oacute;n de la empresa";
$l['all']['BusinessWebsite'] = "Sitio web de la empresa";
$l['all']['BusinessEmail'] = "Email de la empresa";
$l['all']['BusinessContactPerson'] = "Persona contacto en la empresa";
$l['all']['DBPasswordEncryption'] = "Tipo de cifrado de las contrase&ntilde;as en la base de datos";


/* **********************************************************************************
 * Tooltips
 * Helper information such as tooltip text for mouseover events and popup tooltips
 ************************************************************************************/

$l['Tooltip']['Username'] = "Escriba el nombre del usuario";
$l['Tooltip']['UsernameWildcard'] = "Puede utilizar los caracteres * o % como comodines";
$l['Tooltip']['HotspotName'] = "Escriba el nombre del hotspot";
$l['Tooltip']['NasName'] = "Escriba el nombre del NAS";
$l['Tooltip']['GroupName'] = "Escriba el nombre del grupo";
$l['Tooltip']['AttributeName'] = "Escriba el monbre del atributo";
$l['Tooltip']['VendorName'] = "Escriba el nombre del vendedor";
$l['Tooltip']['PoolName'] = "Escriba el nombre del pool";
$l['Tooltip']['IPAddress'] = "Excriba la direcci&oacute;n IP";
$l['Tooltip']['Filter'] = "Escriba un filtro, puede ser cualquier cadena alfanum&eacute;rica. D&eacute;jelo vac&iacute;o para mostrar todo. ";
$l['Tooltip']['Date'] = "Escriba la fecha <br/> ejemplo: 1982-06-04 (yyyy-mm-dd)";
$l['Tooltip']['RateName'] = "Escriba el nombre de la tarifa";
$l['Tooltip']['OperatorName'] = "Escriba el nombre del operadot";
$l['Tooltip']['BillingPlanName'] = "Escriba el nombre del plan de cobro";

$l['Tooltip']['EditRate'] = "Editar tarifa";
$l['Tooltip']['RemoveRate'] = "Eliminar tarifa";

$l['Tooltip']['rateNameTooltip'] = "El nombre de la tarifa,<br/> para describir el prop&oacute;sito de la misma";
$l['Tooltip']['rateTypeTooltip'] = "El tipo de tarifa, para describir<br/> la operaci&oacute;n de la tarifa";
$l['Tooltip']['rateCostTooltip'] = "El costo de la tarifa";
$l['Tooltip']['planNameTooltip'] = "El nombre del plan. Este<br/> es un nombre que describe las caracter&iacute;sticas del plan";
$l['Tooltip']['planIdTooltip'] = "";
$l['Tooltip']['planTimeTypeTooltip'] = "";
$l['Tooltip']['planTimeBankTooltip'] = "";
$l['Tooltip']['planTimeRefillCostTooltip'] = "";
$l['Tooltip']['planTrafficRefillCostTooltip'] = "";
$l['Tooltip']['planBandwidthUpTooltip'] = "";
$l['Tooltip']['planBandwidthDownTooltip'] = "";
$l['Tooltip']['planTrafficTotalTooltip'] = "";
$l['Tooltip']['planTrafficDownTooltip'] = "";
$l['Tooltip']['planTrafficUpTooltip'] = "";

$l['Tooltip']['planRecurringTooltip'] = "";
$l['Tooltip']['planRecurringPeriodTooltip'] = "";
$l['Tooltip']['planCostTooltip'] = "";
$l['Tooltip']['planSetupCostTooltip'] = "";
$l['Tooltip']['planTaxTooltip'] = "";
$l['Tooltip']['planCurrencyTooltip'] = "";
$l['Tooltip']['planGroupTooltip'] = "";

$l['Tooltip']['EditIPPool'] = "Editar pool de IPs";
$l['Tooltip']['RemoveIPPool'] = "Eliminar pool de IPs";
$l['Tooltip']['EditIPAddress'] = "Editar direcci&oacute;n IP";
$l['Tooltip']['RemoveIPAddress'] = "Eliminar direcci&oacute;n IP";

$l['Tooltip']['BusinessNameTooltip'] = "";
$l['Tooltip']['BusinessPhoneTooltip'] = "";
$l['Tooltip']['BusinessAddressTooltip'] = "";
$l['Tooltip']['BusinessWebsiteTooltip'] = "";
$l['Tooltip']['BusinessEmailTooltip'] = "";
$l['Tooltip']['BusinessContactPersonTooltip'] = "";

$l['Tooltip']['proxyNameTooltip'] = "Nombre del proxy";
$l['Tooltip']['proxyRetryDelayTooltip'] = "El tiempo (en segundos) a esperar <br/> la respuesta del proxy, antes de <br/> reenviarle la petici&oacute;n.";
$l['Tooltip']['proxyRetryCountTooltip'] = "El n&uacute;mero de reintentos a intentar <br/> antes de rendire y enviar un mensaje al NAS.";
$l['Tooltip']['proxyDeadTimeTooltip'] = "Si el servidor no responde a ninguno <br/> de los reintentos FreeRADIUS dejar&acute; <br/> de enviar los las peticiones proxy y <br/> marcar&acute; al servidor como 'muerto'.";
$l['Tooltip']['proxyDefaultFallbackTooltip'] = "Si ninguno de los reinos encontrados <br/> responde, probaremos &eacute;ste. <br/>";
$l['Tooltip']['realmNameTooltip'] = "Nombre del reino";
$l['Tooltip']['realmTypeTooltip'] = "Su valor predeterminado es 'radius'";
$l['Tooltip']['realmSecretTooltip'] = "Secreto RADIUS del reino";
$l['Tooltip']['realmAuthhostTooltip'] = "Servidor de autenticaci&oacute;n del reino";
$l['Tooltip']['realmAccthostTooltip'] = "Servidor de conteo del reino";
$l['Tooltip']['realmLdflagTooltip'] = "Permite balanceo de cargas<br/> Los valores permitidos son 'fail_over' <br/> y 'round_robin'.";
$l['Tooltip']['realmNostripTooltip'] = "Eliminar o no el sufijo <br/> del reino";
$l['Tooltip']['realmHintsTooltip'] = "";
$l['Tooltip']['realmNotrealmTooltip'] = "";


$l['Tooltip']['vendorNameTooltip'] = "Ejemplo: Cisco<br/>&nbsp;&nbsp;&nbsp; El nombre del vendedor.<br/>&nbsp;&nbsp;&nbsp;";
$l['Tooltip']['typeTooltip'] = "Ejemplo: string<br/>&nbsp;&nbsp;&nbsp; El tipo datos de atributo<br/>&nbsp;&nbsp;&nbsp; (string, integer, date, ipaddr).";
$l['Tooltip']['attributeTooltip'] = "Ejemplo: Framed-IPAddress<br/>&nbsp;&nbsp;&nbsp;El nombre exacto del atributo.<br/>&nbsp;&nbsp;&nbsp;";

$l['Tooltip']['RecommendedOPTooltip'] = "Ejemplo: :=<br/>&nbsp;&nbsp;&nbsp;El operador de atributo recomendado.<br/>&nbsp;&nbsp;&nbsp;(uno de: := == != etc...)";
$l['Tooltip']['RecommendedTableTooltip'] = "Ejemplo: check<br/>&nbsp;&nbsp;&nbsp; La tabla de destino recomendada.<br/>&nbsp;&nbsp;&nbsp; (o 'check' o 'reply').";
$l['Tooltip']['RecommendedTooltipTooltip'] = "Ejemplo: La direcci&oacute;n IP para el usuario<br/>&nbsp;&nbsp;&nbsp; La sugerencia recomendada.";
$l['Tooltip']['RecommendedHelperTooltip'] = "La funci&oacute;n auxiliar que estar&aacute; disponible<br/>&nbsp;&nbsp;&nbsp;despu&eacute;s de agregar el atributo.";



$l['Tooltip']['AttributeEdit'] = "Editar atributo";


$l['Tooltip']['UserEdit'] = "Editar usuario";
$l['Tooltip']['HotspotEdit'] = "Editar hotspot";
$l['Tooltip']['EditNAS'] = "Editar NAS";
$l['Tooltip']['RemoveNAS'] = "Quitar NAS";

$l['Tooltip']['EditUserGroup'] = "Editar grupo de usuarios";
$l['Tooltip']['ListUserGroups'] = "Listado de grupos de usuarios";

$l['Tooltip']['EditProfile'] = "Editar perfil";

$l['Tooltip']['EditRealm'] = "Editar reino";
$l['Tooltip']['EditProxy'] = "Editar proxy";

$l['Tooltip']['EditGroup'] = "Editar grupo";

$l['FormField']['mngradgroupcheck.php']['ToolTip']['Value'] = "Si especifica un valor, se borrar&aacute; solamente el registro que coincida con el nombre del grupo y con el valor que usted escribi&oacute;. Si deja este campo en blanco, todos los registros asociados a este grupo ser&aacute;n eliminados!";

$l['FormField']['mngradgroupreplydel.php']['ToolTip']['Value'] = "Si especifica un valor, se borrar&aacute; solamente el registro que coincida con el nombre del grupo y con el valor que usted escribi&oacute;. Si deja este campo en blanco, todos los registros asociados a este grupo ser&aacute;n eliminados!";

$l['FormField']['mngradnasnew.php']['ToolTip']['NasShortname'] = "(nombre descriptivo)";

$l['FormField']['mngradusergroupdel.php']['ToolTip']['Groupname'] = "Si especifica un grupo, se borrar&aacute; solamente el registro que conincida con el nombre de usuario y con el grupo que usted escribi&oacute;. Si omite el grupo, todos los registros de este usuario ser&aacute;n eliminados!";


$l['Tooltip']['usernameTooltip'] = "El nombre de usuario con el que<br/>&nbsp;&nbsp;&nbsp; se conectar&aacute; al sistema";
$l['Tooltip']['passwordTypeTooltip'] = "Tipo de contrase&ntilde;a utilizado para autenticar al usuario en Radius.";
$l['Tooltip']['passwordTooltip'] = "Las contrase&ntilde;as son sensibles<br/>&nbsp;&nbsp;&nbsp; a las may&uacute;sculas y min&uacute;sculas en algunos sistemas";
$l['Tooltip']['groupTooltip'] = "El usuario ser&aacute; agregado a este grupo.<br/>&nbsp;&nbsp;&nbsp; Cuando se asigna un grupo a un usuario <br/>&nbsp;&nbsp;&nbsp;el usuario est&aacute; sujeto a los atributos de ese grupo.";
$l['Tooltip']['macaddressTooltip'] = "Ejemplo: 00-AA-BB-CC-DD-EE<br/>&nbsp;&nbsp;&nbsp;El formato de la direcci&oacute;n MAC debe ser <br/>&nbsp;&nbsp;&nbsp;igual al que env&iacute;a el NAS. La mayor&iacute;a de las veces no lleva<br/>&nbsp;&nbsp;&nbsp;separadores.";
$l['Tooltip']['pincodeTooltip'] = "Ejemplo: khrivnxufi101<br/>&nbsp;&nbsp;&nbsp; Este es el c&oacute;digo PIN que el usuari debe escribir.<br/>&nbsp;&nbsp;&nbsp;Puede usar caracteres alfanum&eacute;ricos, se diferencian las may&uacute;sculas de las min&uacute;sculas";
$l['Tooltip']['usernamePrefixTooltip'] = "Ejemplo: TMP_ POP_ WIFI1_ <br/>&nbsp;&nbsp;&nbsp; Este prefijo se le agregar&aacute; al usuario <br/>&nbsp;&nbsp;&nbsp;una vez creado.";
$l['Tooltip']['instancesToCreateTooltip'] = "Ejemplo: 100<br/>&nbsp;&nbsp;&nbsp;La cantidad de usuarios aleatorios a crear<br/>&nbsp;&nbsp;&nbsp;con el perfil especificado.";
$l['Tooltip']['lengthOfUsernameTooltip'] = "Ejemplo: 8<br/>&nbsp;&nbsp;&nbsp;El largo de los nombres de usuario<br/>&nbsp;&nbsp;&nbsp;a crear. Se recomiendan 8<F4>12 caracteres.";
$l['Tooltip']['lengthOfPasswordTooltip'] = "Ejemplo: 8<br/>&nbsp;&nbsp;&nbsp;El largo de las contrase&ntilde;as<br/>&nbsp;&nbsp;&nbsp;a crear. Se recomiendan 8<F4>12 caracteres.";


$l['Tooltip']['hotspotNameTooltip'] = "Ejemplo: Hotel Stratocaster<br/>&nbsp;&nbsp;&nbsp;un nombre para el hotspot<br/>";

$l['Tooltip']['hotspotMacaddressTooltip'] = "Ejemplo: 00:aa:bb:cc:dd:ee<br/>&nbsp;&nbsp;&nbsp;La direcci&oacute;n MAC del NAS<br/>";

$l['Tooltip']['geocodeTooltip'] = "Ejemplo: -1.002,-2.201<br/>&nbsp;&nbsp;&nbsp;esta es el c&oacute;digo de localizaci&oacute;n de GooleMaps<br/>&nbsp;&nbsp;&nbsp;para ubicar al Hotspot/NAS en el mapa (ver GIS).";


/* ********************************************************************************** */




/* **********************************************************************************
 * Links and Buttons
 ************************************************************************************/
$l['button']['ClearSessions'] = "Limpiar sesiones";

$l['button']['ListRates'] = "Listado de tarifas";
$l['button']['NewRate'] = "Nueva tarifa";
$l['button']['EditRate'] = "Editar tarifa";
$l['button']['RemoveRate'] = "Eliminar tarifa";

$l['button']['ListPlans'] = "Listado de Planes";
$l['button']['NewPlan'] = "Nuevo plan";
$l['button']['EditPlan'] = "Editar plan";
$l['button']['RemovePlan'] = "Eliminar plan";

$l['button']['ListRealms'] = "Listado de reinos";
$l['button']['NewRealm'] = "Nuevo reino";
$l['button']['EditRealm'] = "Editar reino";
$l['button']['RemoveRealm'] = "Eliminar reino";

$l['button']['ListProxys'] = "Listado de proxys";
$l['button']['NewProxy'] = "Nuevo proxy";
$l['button']['EditProxy'] = "Editar proxy";
$l['button']['RemoveProxy'] = "Eliminar proxy";

$l['button']['ListAttributesforVendor'] = "Listado de atributos:";
$l['button']['NewVendorAttribute'] = "Nuevo atributo";
$l['button']['EditVendorAttribute'] = "Editar atributo";
$l['button']['SearchVendorAttribute'] = "Buscar atributo";
$l['button']['RemoveVendorAttribute'] = "Eliminar atributo";
$l['button']['ImportVendorDictionary'] = "Importar diccionario";


$l['button']['BetweenDates'] = "Entre las fechas:";
$l['button']['Where'] = "Donde";
$l['button']['AccountingFieldsinQuery'] = "Campos de conteo en la consulta:";
$l['button']['OrderBy'] = "Ordenar por";
$l['button']['HotspotAccounting'] = "Conteo de hotspot";
$l['button']['HotspotsComparison'] = "Compara&oacute;n entre hotspots";

$l['button']['CleanupStaleSessions'] = "Limpiar las sesiones vencidas";
$l['button']['DeleteAccountingRecords'] = "Eliminar registros de conteo";

$l['button']['ListUsers'] = "Listado de usuarios";
$l['button']['NewUser'] = "Nuevo usuario";
$l['button']['NewUserQuick'] = "Nuevo usuario - Modo expreso";
$l['button']['BatchAddUsers'] = "Agregar usuarios por lotes";
$l['button']['EditUser'] = "Editar usuario";
$l['button']['SearchUsers'] = "Buscar usuarios";
$l['button']['RemoveUsers'] = "Eliminar usuarios";
$l['button']['ListHotspots'] = "Listado de hotspots";
$l['button']['NewHotspot'] = "Nuevo hotspot";
$l['button']['EditHotspot'] = "Editar hotspot";
$l['button']['RemoveHotspot'] = "Eliminar Hotspot";

$l['button']['ListIPPools'] = "Listado de pools de IPs";
$l['button']['NewIPPool'] = "Nuevo pool deIPs";
$l['button']['EditIPPool'] = "Editar pool de IPs";
$l['button']['RemoveIPPool'] = "Eliminar pool de IPs";

$l['button']['ListNAS'] = "Listado de NAS";
$l['button']['NewNAS'] = "Nuevo NAS";
$l['button']['EditNAS'] = "Editar NAS";
$l['button']['RemoveNAS'] = "Eliminar NAS";

$l['button']['ListUserGroup'] = "Listado de mapeos Usuario-Grupo";
$l['button']['ListUsersGroup'] = "Listado de los grupos de un usuario";
$l['button']['NewUserGroup'] = "Nuevo mapeo Usuario-Grupo";
$l['button']['EditUserGroup'] = "Editar mapeo Usuario-Grupo";
$l['button']['RemoveUserGroup'] = "Eliminar mapeo Usuario-Grupo";

$l['button']['ListProfiles'] = "Listado de perfiles";
$l['button']['NewProfile'] = "Nuevo perfil";
$l['button']['EditProfile'] = "Editar perfil";
$l['button']['DuplicateProfile'] = "Duplicar perfil";
$l['button']['RemoveProfile'] = "Eliminar perfil";

$l['button']['ListGroupReply'] = "Listado de grupos de respuestas";
$l['button']['SearchGroupReply'] = "Buscar grupo de respuestas";
$l['button']['NewGroupReply'] = "Nuevo grupo de respuestas";
$l['button']['EditGroupReply'] = "Editar grupo de respuestas";
$l['button']['RemoveGroupReply'] = "Eliminar grupo de respuestas";

$l['button']['ListGroupCheck'] = "Listado de grupos de verificaciones";
$l['button']['SearchGroupCheck'] = "Buscar grupo de verificaciones";
$l['button']['NewGroupCheck'] = "Nuevo grupo de verificaciones";
$l['button']['EditGroupCheck'] = "Editar grupo de verificaciones";
$l['button']['RemoveGroupCheck'] = "Eliminar grupo de verificaciones";

$l['button']['UserAccounting'] = "Conteo por usuario";
$l['button']['IPAccounting'] = "Conteo por IP";
$l['button']['NASIPAccounting'] = "Conteo por IP de NAS";
$l['button']['DateAccounting'] = "Conteo por fecha";
$l['button']['AllRecords'] = "Todos los registros";
$l['button']['ActiveRecords'] = "Registros activos";

$l['button']['OnlineUsers'] = "Usuarios en l&iacute;nea";
$l['button']['LastConnectionAttempts'] = "Ultimos intentos de conexi&oacute;n";
$l['button']['TopUser'] = "Usuario tope";
$l['button']['History'] = "Historial";

$l['button']['ServerStatus'] = "Estado del servidor";
$l['button']['ServicesStatus'] = "Estado de los servicios";

$l['button']['daloRADIUSLog'] = "Registro de daloRADIUS";
$l['button']['RadiusLog'] = "Registro de FreeRADIUS";
$l['button']['SystemLog'] = "Registro del sistema";
$l['button']['BootLog'] = "Registro de arranque";

$l['button']['UserLogins'] = "Accesos del usuario";
$l['button']['UserDownloads'] = "Descargas del usuario";
$l['button']['UserUploads'] = "Subidas del usuario";
$l['button']['TotalLogins'] = "Accesos totales";
$l['button']['TotalTraffic'] = "Tr&aacute;fico total";

$l['button']['ViewMAP'] = "Ver MAP";
$l['button']['EditMAP'] = "Editar MAP";
$l['button']['RegisterGoogleMapsAPI'] = "Registrar API de GoogleMaps";

$l['button']['DatabaseSettings'] = "Ajustes de la base de datos";
$l['button']['LanguageSettings'] = "Ajustes de lenguaje";
$l['button']['LoggingSettings'] = "Ajustes de acceso";
$l['button']['InterfaceSettings'] = "Ajustes de la interfase";

$l['button']['TestUserConnectivity'] = "Probar la conectividad del usuario";
$l['button']['DisconnectUser'] = "Desconectar usuario";

$l['button']['ManageBackups'] = "Administrar respaldos";
$l['button']['CreateBackups'] = "Crear respaldos";

$l['button']['ListOperators'] = "Listado de operadores";
$l['button']['NewOperator'] = "Nuevo operador";
$l['button']['EditOperator'] = "Editar operador";
$l['button']['RemoveOperator'] = "Eliminar operador";

$l['button']['ProcessQuery'] = "Procesar consulta";



/* ********************************************************************************** */


/* **********************************************************************************
 * Titles
 * The text related to all the title headers in captions,tables and tabbed layout text
 ************************************************************************************/

$l['title']['RateInfo'] = "Infomaci&oacute;n de tarifa";
$l['title']['PlanInfo'] = "Informaci&oacute;n de plan";
$l['title']['TimeSettings'] = "Ajustes de tiempo";
$l['title']['BandwidthSettings'] = "Ajustes de ancho de banda";
$l['title']['PlanRemoval'] = "Remoci&oacute;n de plan";

$l['title']['Backups'] = "Respaldos";
$l['title']['FreeRADIUSTables'] = "Tablas de FreeRADIUS";
$l['title']['daloRADIUSTables'] = "Tablas de daloRADIUS";

$l['title']['IPPoolInfo'] = "Informaci&oacute;n del pool de IPs";

$l['title']['BusinessInfo'] = "Informaci&oacute;n del negocio";

$l['title']['CleanupRecords'] = "Limpiar registros";
$l['title']['DeleteRecords'] = "Eliminar registros";

$l['title']['RealmInfo'] = "Informaci&oacute;n del reino";

$l['title']['ProxyInfo'] = "Informaci&oacute;n del proxy";

$l['title']['VendorAttribute'] = "Atributo del vendedor";

$l['title']['AccountRemoval'] = "Remoci&oacute;n de cuenta";
$l['title']['AccountInfo'] = "Informaci&oacute;n de la cuenta";

$l['title']['Profiles'] = "Perfiles";
$l['title']['ProfileInfo'] = "Informaci&oacute;n del perfil";

$l['title']['GroupInfo'] = "Informaci&oacute;n del grupo";
$l['title']['GroupAttributes'] = "Atributos del grupo";

$l['title']['NASInfo'] = "Informaci&oacute;n del NAS";
$l['title']['NASAdvanced'] = "NAS avanzado";

$l['title']['UserInfo'] = "Informaci&oacute;n del usuario";
$l['title']['BillingInfo'] = "Informaci&oacute;n de cobro";

$l['title']['Attributes'] = "Atributos";
$l['title']['ProfileAttributes'] = "Atributos del perfil";

$l['title']['HotspotInfo'] = "Informaci&oacute;n del hotspot";
$l['title']['HotspotRemoval'] = "Remoci&oacute;n de hotspot";

$l['title']['ContactInfo'] = "Informaci&oacute;n de contacto";

$l['title']['Plan'] = "Plan";

$l['title']['Profile'] = "Perfil";
$l['title']['Groups'] = "Grupos";
$l['title']['RADIUSCheck'] = "Atributos a verificar";
$l['title']['RADIUSReply'] = "Atributos de respuesta";

$l['title']['Settings'] = "Configuraci&oacute;n";
$l['title']['DatabaseSettings'] = "Configuraci&oacute;n de la base de datos";
$l['title']['DatabaseTables'] = "Tablas de la base de datos";
$l['title']['AdvancedSettings'] = "Configuraci&oacute;n avanzada";

$l['title']['Advanced'] = "Avanzado";
$l['title']['Optional'] = "Opcional";

/* ********************************************************************************** */


/* **********************************************************************************
 * Text
 * General text information that is used through-out the pages
 ************************************************************************************/

$l['text']['LoginRequired'] = "Se requiere que acceda al sistema";
$l['text']['LoginPlease'] = "Por favor, acceda al sistema";

/* ********************************************************************************** */



/* **********************************************************************************
 * Contact Info
 * Related to all contact info text, user info, hotspot owner contact information etc
 ************************************************************************************/

$l['ContactInfo']['FirstName'] = "Nombre(s)";
$l['ContactInfo']['LastName'] = "Apellido(s)";
$l['ContactInfo']['Email'] = "Correo electr&oacute;nico";
$l['ContactInfo']['Department'] = "Departamento";
$l['ContactInfo']['WorkPhone'] = "Tel&eacute;fono de trabajo";
$l['ContactInfo']['HomePhone'] = "Tel&eacute;fono de habitaci&oacute;n";
$l['ContactInfo']['Phone'] = "Te^&eacute;fono";
$l['ContactInfo']['MobilePhone'] = "Tel&eacute;fono m&oacute;vil";
$l['ContactInfo']['Notes'] = "Notas";
$l['ContactInfo']['EnableUserUpdate'] = "El usuario puede actualizar su informaci&oacute;n";

$l['ContactInfo']['OwnerName'] = "Nombre del propietario";
$l['ContactInfo']['OwnerEmail'] = "Correo del propietario";
$l['ContactInfo']['ManagerName'] = "Nombre del administrador";
$l['ContactInfo']['ManagerEmail'] = "Correo del administrador";
$l['ContactInfo']['Company'] = "Compa&ntilde;&iacute;a";
$l['ContactInfo']['Address'] = "Direcci&oacute;n";
$l['ContactInfo']['City'] = "Ciudad";
$l['ContactInfo']['State'] = "Estado";
$l['ContactInfo']['Zip'] = "C&oacute;digo postal";
$l['ContactInfo']['Phone1'] = "Tel&eacute;fono 1";
$l['ContactInfo']['Phone2'] = "Tel&eacute;fono 2";
$l['ContactInfo']['HotspotType'] = "Tipo de hotspot";
$l['ContactInfo']['CompanyWebsite'] = "Sitio web de la compa&ntilde;&iacute;a";
$l['ContactInfo']['CompanyPhone'] = "Tel&eacute;fono de la compa&ntilde;&iacute;a";
$l['ContactInfo']['CompanyEmail'] = "Correo de la compa&ntilde;&iacute;a";
$l['ContactInfo']['CompanyContact'] = "Contacto de la compa&ntilde;&iacute;a";

$l['ContactInfo']['PlanName'] = "Nombre del plan";
$l['ContactInfo']['ContactPerson'] = "Persona contacto";
$l['ContactInfo']['PaymentMethod'] = "Me&eacute;todo de pago";
$l['ContactInfo']['Cash'] = "Efectivo";
$l['ContactInfo']['CreditCardNumber'] = "N&uacute;mero de la tarjeta de cr&eacute;dito";
$l['ContactInfo']['CreditCardName'] = "Nombre en la tarjeta";
$l['ContactInfo']['CreditCardVerificationNumber'] = "N&uacute;mero de verificaci&oacute;n";
$l['ContactInfo']['CreditCardType'] = "Tipo de tarjeta de cr&eacute;dito";
$l['ContactInfo']['CreditCardExpiration'] = "Fecha de expiraci&oacute;n";

/* ********************************************************************************** */


$l['Intro']['billhistorymain.php'] = "Hist&oacute;rico de cobro";
$l['Intro']['msgerrorpermissions.php'] = "Error de permisos";

$l['Intro']['mngradproxys.php'] = "Administraci&oacute;n de proxys";
$l['Intro']['mngradproxysnew.php'] = "Nuevo proxy";
$l['Intro']['mngradproxyslist.php'] = "Listado de proxys";
$l['Intro']['mngradproxysedit.php'] = "Editar proxy";
$l['Intro']['mngradproxysdel.php'] = "Remover proxy";

$l['Intro']['mngradrealms.php'] = "Administraci&oacute;n de reinos";
$l['Intro']['mngradrealmsnew.php'] = "Reino nuevo";
$l['Intro']['mngradrealmslist.php'] = "Listado de reinos";
$l['Intro']['mngradrealmsedit.php'] = "Editar reino";
$l['Intro']['mngradrealmsdel.php'] = "Remover reino";

$l['Intro']['mngradattributes.php'] = "Administraci&oacute;n de los atributos de los vendedores";
$l['Intro']['mngradattributeslist.php'] = "Listado de atributos";
$l['Intro']['mngradattributesnew.php'] = "Atributo nuevo";
$l['Intro']['mngradattributesedit.php'] = "Editar atributos";
$l['Intro']['mngradattributessearch.php'] = "Buscar atributos";
$l['Intro']['mngradattributesdel.php'] = "Remover atributos";
$l['Intro']['mngradattributesimport.php'] = "Importar diccionario";


$l['Intro']['acctactive.php'] = "Informaci&oacute;n de registros activos";
$l['Intro']['acctall.php'] = "Informaci&oacute;n de todos los usuarios";
$l['Intro']['acctdate.php'] = "Informaci&oacute;n ordenado por fecha";
$l['Intro']['accthotspot.php'] = "Informaci&oacute;n de Hotspots";
$l['Intro']['acctipaddress.php'] = "Informaci&oacute;n de IPs";
$l['Intro']['accthotspotcompare.php'] = "Comparaci&oacite;n entre Hotspots";
$l['Intro']['acctmain.php'] = "Informaci&oacute;n";
$l['Intro']['acctnasipaddress.php'] = "NAS IP Accounting";
$l['Intro']['acctusername.php'] = "Informaci&oacute;n de usuarios";
$l['Intro']['acctcustom.php'] = "Informaci&oacute;n personalizado";
$l['Intro']['acctcustomquery.php'] = "Consulta personalizada";
$l['Intro']['acctmaintenance.php'] = "Mantenimiento de informaci&oacute;n de registros";
$l['Intro']['acctmaintenancecleanup.php'] = "Limpieza de conexiones atascadas";
$l['Intro']['acctmaintenancedelete.php'] = "Eliminar informaci&oacute;n de registros";

$l['Intro']['billmain.php'] = "Cobros";
$l['Intro']['ratesmain.php'] = "Tarifas";
$l['Intro']['billratesdate.php'] = "Informaci&oacute;n de tarifas prepago";
$l['Intro']['billratesdel.php'] = "Eliminar tarifa";
$l['Intro']['billratesedit.php'] = "Editar detalles de tarifa";
$l['Intro']['billrateslist.php'] = "Tabla de tarifas";
$l['Intro']['billratesnew.php'] = "Nueva tarifa";

$l['Intro']['paypalmain.php'] = "Transacciones por PayPal";
$l['Intro']['billpaypaltransactions.php'] = "Transacciones por PayPal";

$l['Intro']['billhistoryquery.php'] = "Hist&oacute;rico de cobros";

$l['Intro']['billplans.php'] = "Planes de pago";
$l['Intro']['billplansdel.php'] = "Eliminar plan";
$l['Intro']['billplansedit.php'] = "Editar detalles del plan";
$l['Intro']['billplanslist.php'] = "Tabla de planes";
$l['Intro']['billplansnew.php'] = "Nuevo plan";

$l['Intro']['billpos.php'] = "Cobro de los puntos de venta";
$l['Intro']['billposdel.php'] = "Eliminar usuario";
$l['Intro']['billposedit.php'] = "Editar usuarios";
$l['Intro']['billposlist.php'] = "Listado de usuarios";
$l['Intro']['billposnew.php'] = "Nuevo usuario";

$l['Intro']['giseditmap.php'] = "Editar modo MAP";
$l['Intro']['gismain.php'] = "Mapeo GIS";
$l['Intro']['gisviewmap.php'] = "Ver modo MAP";

$l['Intro']['graphmain.php'] = "Gr&aacute;ficos de uso";
$l['Intro']['graphsalltimetrafficcompare.php'] = "Total Traffic Comparison Usage";
$l['Intro']['graphsalltimelogins.php'] = "Total accesos";
$l['Intro']['graphsoveralldownload.php'] = "Descargas por usuario";
$l['Intro']['graphsoveralllogins.php'] = "Acceso por usuario";
$l['Intro']['graphsoverallupload.php'] = "Subidas por usuario";

$l['Intro']['rephistory.php'] = "iHistorial de acciones";
$l['Intro']['replastconnect.php'] = "Ultimos 50 intentos de conexi&oacute;n";
$l['Intro']['repstatradius.php'] = "Informaci&oacute;n de los servicios (daemons)";
$l['Intro']['repstatserver.php'] = "Informaci&oacute;n y estado del servidor";
$l['Intro']['reponline.php'] = "Listado de usuarios en l&iacute;nea";
$l['Intro']['replogssystem.php'] = "Registro del sistema";
$l['Intro']['replogsradius.php'] = "Registro del servidor RADIUS";
$l['Intro']['replogsdaloradius.php'] = "Registro de daloRADIUS";
$l['Intro']['replogsboot.php'] = "Registro de inicio del sistema";
$l['Intro']['replogs.php'] = "Registros";

$l['Intro']['rephsall.php'] = "Listado de Hotspots";
$l['Intro']['repmain.php'] = "Reportes";
$l['Intro']['repstatus.php'] = "Estado";
$l['Intro']['replogs.php'] = "Registros";
$l['Intro']['reptopusers.php'] = "Top de usuarios";
$l['Intro']['repusername.php'] = "Listado de usuarios";

$l['Intro']['mngbatch.php'] = "Crear usuarios por lotes";
$l['Intro']['mngdel.php'] = "Eliminar usuario";
$l['Intro']['mngedit.php'] = "Editar detalles del usuario";
$l['Intro']['mnglistall.php'] = "Listado de usuarios";
$l['Intro']['mngmain.php'] = "Administraci&oacute;n de Hotspots y de usuarios";
$l['Intro']['mngnew.php'] = "Nuevo usuario";
$l['Intro']['mngnewquick.php'] = "Agregar usuario (modo r&aacute;pido)";
$l['Intro']['mngsearch.php'] = "Buscar usuario";

$l['Intro']['mnghsdel.php'] = "Eliminar Hotspots";
$l['Intro']['mnghsedit.php'] = "Editar detalles de Hotspot";
$l['Intro']['mnghslist.php'] = "Listado de Hotspots";
$l['Intro']['mnghsnew.php'] = "Nuevo Hotspot";

$l['Intro']['mngradusergroupdel.php'] = "Eliminar Mapeo Usuario-Grupo";
$l['Intro']['mngradusergroup.php'] = "Configuraci&oacute;n Usuario-Grupo";
$l['Intro']['mngradusergroupnew.php'] = "Nuevo mapeo Usuario-Grupo";
$l['Intro']['mngradusergrouplist'] = "Listado de los mapeos Usuario-Grupo";
$l['Intro']['mngradusergrouplistuser'] = "Listado de los usuarios de un mapeo Usuario-Grupo";
$l['Intro']['mngradusergroupedit'] = "Editar mapeo Usuario-Grupo para el Usuario:";

$l['Intro']['mngradippool.php'] = "Configuraci&oacute;n de los pool de IPs";
$l['Intro']['mngradippoolnew.php'] = "Nuevo pool de IPs";
$l['Intro']['mngradippoollist.php'] = "Listado de los pools de IPs";
$l['Intro']['mngradippooledit.php'] = "Editar pool de IPs";
$l['Intro']['mngradippooldel.php'] = "Eliminar pool de IPs";

$l['Intro']['mngradnas.php'] = "Configuraci&oacute;n de NAS";
$l['Intro']['mngradnasnew.php'] = "Nuevo NAS";
$l['Intro']['mngradnaslist.php'] = "Listado de NAS";
$l['Intro']['mngradnasedit.php'] = "Editar NAS";
$l['Intro']['mngradnasdel.php'] = "Eliminar NAS";

$l['Intro']['mngradprofiles.php'] = "Configuraci&oacute;n de perfiles";
$l['Intro']['mngradprofilesedit.php'] = "Editar perfiles";
$l['Intro']['mngradprofilesduplicate.php'] = "Perfiles duplicados";
$l['Intro']['mngradprofilesdel.php'] = "Eliminar perfiles";
$l['Intro']['mngradprofileslist.php'] = "Listado de perfiles";
$l['Intro']['mngradprofilesnew.php'] = "Nuevo perfil";

$l['Intro']['mngradgroups.php'] = "Configuraci&oacute;n de grupos";

$l['Intro']['mngradgroupreplynew.php'] = "Nuevo grupo de respuestas";
$l['Intro']['mngradgroupreplylist.php'] = "Listado de grupos de respuestas";
$l['Intro']['mngradgroupreplyedit.php'] = "Editar grupo de respuestas de un Grupo:";
$l['Intro']['mngradgroupreplydel.php'] = "Eliminar grupo de respuesta";
$l['Intro']['mngradgroupreplysearch.php'] = "Buscar grupo de respuesta";

$l['Intro']['mngradgroupchecknew.php'] = "Nuevo grupo de verificaciones";
$l['Intro']['mngradgroupchecklist.php'] = "Listado de grupos de verificaciones";
$l['Intro']['mngradgroupcheckedit.php'] = "Editar grupos de verificaciones para un Grupo:";
$l['Intro']['mngradgroupcheckdel.php'] = "Eliminar grupo de verificaciones";
$l['Intro']['mngradgroupchecksearch.php'] = "Buscar grupo de verificaciones";

$l['Intro']['configdb.php'] = "Configuraci&oacute;n de la base de datos";
$l['Intro']['configlang.php'] = "Configuraci&oacute;n de idioma";
$l['Intro']['configlogging.php'] = "Configuraci&oacute;n de los registros";
$l['Intro']['configinterface.php'] = "Configuraci&oacute;n de la interfase web";
$l['Intro']['configmainttestuser.php'] = "Probar la conectividad del usuario";
$l['Intro']['configmain.php'] = "Configuraci&oacute;n de la base de datos";
$l['Intro']['configmaint.php'] = "Mantenimiento";
$l['Intro']['configmaintdisconnectuser.php'] = "Desconectar usuario";
$l['Intro']['configbusiness.php'] = "Detalle de la empresa";
$l['Intro']['configbusinessinfo.php'] = "Informaci&oacute;n de la empresa";
$l['Intro']['configbackup.php'] = "Respaldo";
$l['Intro']['configbackupcreatebackups.php'] = "Crear respaldos";
$l['Intro']['configbackupmanagebackups.php'] = "Administrar respaldos";

$l['Intro']['configoperators.php'] = "Configuraci&oacute;n de los operadores";
$l['Intro']['configoperatorsdel.php'] = "Eliminar operador";
$l['Intro']['configoperatorsedit.php'] = "Editar la configuraci&oacute;n de un operador";
$l['Intro']['configoperatorsnew.php'] = "Nuevo operador";
$l['Intro']['configoperatorslist.php'] = "Listado de operadores";

$l['Intro']['login.php'] = "Acceso";

$l['captions']['providebillratetodel'] = "Escriba el tipo de tarifa que desea eliminar";
$l['captions']['detailsofnewrate'] = "Ahora complete los detelles de la nueva tarifa";
$l['captions']['filldetailsofnewrate'] = "Complete los detelles de la nueva tarifa";

/* **********************************************************************************
 * Help Pages Info
 * Each page has a header which is the Intro class, when clicking on the header
 * it will reveal/hide a helpPage div content which is a description of a specific
 * page, basically your expanded tool-tip.
 ************************************************************************************/


$l['helpPage']['login'] = "";

$l['helpPage']['billpaypaltransactions'] = "Listado de todas las transacciones de PayPal";
$l['helpPage']['billhistoryquery'] = "Listado del historial de facturaci&oacute;n de uno o varios usuarios";

$l['helpPage']['billplanslist'] = "";
$l['helpPage']['billplansnew'] = "";
$l['helpPage']['billplansedit'] = "";
$l['helpPage']['billplansdel'] = "";

$l['helpPage']['billposlist'] = "";
$l['helpPage']['billposnew'] = "";
$l['helpPage']['billposedit'] = "";
$l['helpPage']['billposdel'] = "";

$l['helpPage']['billrateslist'] = "";
$l['helpPage']['billratesnew'] = "";
$l['helpPage']['billratesedit'] = "";
$l['helpPage']['billratesdel'] = "";
$l['helpPage']['billratesdate'] = "";

$l['helpPage']['mngradproxys'] = "";
$l['helpPage']['mngradproxyslist'] = "";
$l['helpPage']['mngradproxysnew'] = "";
$l['helpPage']['mngradproxysedit'] = "";
$l['helpPage']['mngradproxysdel'] = "";

$l['helpPage']['mngradrealms'] = "";
$l['helpPage']['mngradrealmslist'] = "";
$l['helpPage']['mngradrealmsnew'] = "";
$l['helpPage']['mngradrealmsedit'] = "";
$l['helpPage']['mngradrealmsdel'] = "";

$l['helpPage']['mngradattributes'] = "";
$l['helpPage']['mngradattributeslist'] = "";
$l['helpPage']['mngradattributesnew'] = "";
$l['helpPage']['mngradattributesedit'] = "";
$l['helpPage']['mngradattributessearch'] = "";
$l['helpPage']['mngradattributesdel'] = "";
$l['helpPage']['mngradattributesimport'] = "";

$l['helpPage']['msgerrorpermissions'] = "No tiene permisos para acceder a esta p&aacute;gina. <br/>
Por favor acuda al Administrador de Sistemas. <br/>";

$l['helpPage']['mngradusergroup'] = "";
$l['helpPage']['mngradusergroupdel'] = "Debe escribir el nombre del usuario para poder borrarlo";
$l['helpPage']['mngradusergroupnew'] = "";
$l['helpPage']['mngradusergrouplist'] = "";
$l['helpPage']['mngradusergrouplistuser'] = "";
$l['helpPage']['mngradusergroupedit'] = "";


$l['helpPage']['mngradprofiles'] = "
<b>Administraci&oacute;n  de Perfiles</b> - Administre perfiles para los usuarios creando un conjunto de Atributos de Respuesta y de Verificaci&oacute;n <br/>
Los perfiles se pueden ver como la mezcla de un mapeo de respuesta a un grupo (Group Reply) con un mapeo de verificaci&oacute;n a un grupo (Group Check). <br/>
<h200><b>Listar perfiles </b></h200> - Listar perfiles <br/>
<h200><b>Nuevo perfil </b></h200> - Agregar un perfil <br/>
<h200><b>Editar perfil </b></h200> - Editar un perfil <br/>
<h200><b>Eliminar perfil </b></h200> - Eliminar un perfil <br/>
";
$l['helpPage']['mngradprofilesedit'] = "
<h200><b>Editar perfil </b></h200> - Editar un perfil <br/>
";
$l['helpPage']['mngradprofilesdel'] = "
<h200><b>Eliminar perfil </b></h200> - Eliminar un perfil <br/>
";
$l['helpPage']['mngradprofilesduplicate'] = "
<h200><b>Duplicar perfil </b></h200> - Duplicar el conjunto de atributos en un perfil particular en un perfil nuevo <br/>
";
$l['helpPage']['mngradprofileslist'] = "
<h200><b>Listar perfiles </b></h200> - Listar perfiles <br/>
";
$l['helpPage']['mngradprofilesnew'] = "
<h200><b>Nuevo perfil </b></h200> - Agregar un perfil <br/>
";

$l['helpPage']['mngradgroups'] = "
<b>Administraci&oacute;n de grupos</b> - Administre lsa respuestas y verificaciones de los grupos (radgroupreply/radgroupcheck tables).<br/>
<h200><b>Listar Grupos de Respuesta/Verificaci&oacute;n </b></h200> - List Group Reply/Check Mappings<br/>
<h200><b>Buscar Grupos de Respuesta/Verificaci&oacute;nGroup Reply/Check </b></h200> - Busca un Grupos de Respuesta/Verificaci&oacute;n (puede usar comodines) <br/>
<h200><b>Nuevo Grupos de Respuesta/Verificaci&oacute;nGroup Reply/Check </b></h200> - Crea un Grupos de Respuesta/Verificaci&oacute;n <br/>
<h200><b>Editar Grupos de Respuesta/Verificaci&oacute;nGroup Reply/Check </b></h200> - Edita un Grupo de Respuesta/Verificaci&oacute;n <br/>
<h200><b>Eliminar Grupos de Respuesta/Verificaci&oacute;nGroup Reply/Check </b></h200> - Elimina un Grupo de Respuesta/Verificaci&oacute;n <br/>
";


$l['helpPage']['mngradgroupchecknew'] = "
<h200><b>New Group Check </b></h200> - Add a Group Check Mapping <br/>
";
$l['helpPage']['mngradgroupcheckdel'] = "
<h200><b>Delete Group Check </b></h200> - Delete a Group Check Mapping <br/>
";

$l['helpPage']['mngradgroupchecklist'] = "
<h200><b>List Group Check </b></h200> - List Group Check Mappings<br/>
";
$l['helpPage']['mngradgroupcheckedit'] = "
<h200><b>Edit Group Check </b></h200> - Edit a Group Check Mapping <br/>
";
$l['helpPage']['mngradgroupchecksearch'] = "
<h200><b>Search Group Check </b></h200> - Search a Group Check Mapping <br/>
to use a wildcard you may either type the % character which is familiar in SQL or you may use the more common *
for convenience reasons and daloRADIUS will translate it to %
";

$l['helpPage']['mngradgroupreplynew'] = "
<h200><b>New Group Reply </b></h200> - Add a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplydel'] = "
<h200><b>Delete Group Reply </b></h200> - Delete a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplylist'] = "
<h200><b>List Group Reply </b></h200> - List Group Reply Mappings<br/>
";
$l['helpPage']['mngradgroupreplyedit'] = "
<h200><b>Edit Group Reply </b></h200> - Edit a Group Reply Mapping <br/>
";
$l['helpPage']['mngradgroupreplysearch'] = "
<h200><b>Search Group Reply </b></h200> - Search a Group Reply </ Mapping <br/>
to use a wildcard you may either type the % character which is familiar in SQL or you may use the more common *
for convenience reasons and daloRADIUS will translate it to %
";


$l['helpPage']['mngradippool'] = "
<h200><b>List IP Pools</b></h200> - List Configured IP Pools and their assigned IP Addresses <br/>
<h200><b>New IP Pool</b></h200> - Add a new IP Address to a configured IP Pool <br/>
<h200><b>Edit IP Pool</b></h200> - Edit an IP Address for a configured IP Pool <br/>
<h200><b>Remove IP Pool</b></h200> - Remove an IP Address from a configured IP Pool <br/>
";
$l['helpPage']['mngradippoollist'] = "<h200><b>List IP Pools</b></h200> - List Configured IP Pools and their assigned IP Addresses <br/>";
$l['helpPage']['mngradippoolnew'] = "<h200><b>New IP Pool</b></h200> - Add a new IP Address to a configured IP Pool <br/>";
$l['helpPage']['mngradippooledit'] = "<h200><b>Edit IP Pool</b></h200> - Edit an IP Address for a configured IP Pool <br/>";
$l['helpPage']['mngradippooldel'] = "<h200><b>Remove IP Pool</b></h200> - Remove an IP Address from a configured IP Pool <br/>";


$l['helpPage']['mngradnas'] = "";
$l['helpPage']['mngradnasdel'] = "To remove a nas ip/host entry from the database you must provide the ip/host of the account";
$l['helpPage']['mngradnasnew'] = "";
$l['helpPage']['mngradnaslist'] = "";
$l['helpPage']['mngradnasedit'] = "";



$l['helpPage']['mnghsdel'] = "To remove a hotspot from the database you must provide the hotspot's name<br/>";
$l['helpPage']['mnghsedit'] = "You may edit below details for hotspot<br/>";
$l['helpPage']['mnghsnew'] = "You may fill below details for new hotspot addition to database";
$l['helpPage']['mnghslist'] = "Listado de of all hotspots in the database. You may use the quick links to edit or delete a hotspot from the database.";

$l['helpPage']['configdb'] = "
<b>Database Settings</b> - Configure database engine, connection settings, tables names if the
default are not used, and the password encryption type in the database.<br/>
<h200><b>Global Settings</b></h200> - Database Storage Engine<br/>
<h200><b>Tables Settings</b></h200> - If not using the default FreeRADIUS schema you may change the names
of the table names<br/>
<h200><b>Advanced Settings</b></h200> - If you wish to store passwords for users in the database not in
plain text but rather have it encrypted somehow you may choose one of MD5 or Crypt<br/>
";
$l['helpPage']['configlang'] = "
<h200><b>Language Settings</b></h200> - Configure interface language.<br/>
";
$l['helpPage']['configlogging'] = "
<h200><b>Logging Settings</b></h200> - Configure logging rules and facilities <br/>
Please make sure that the filename that you specify has write permissions by the webserver<br/>";
$l['helpPage']['configinterface'] = "
<h200><b>Interface Settings</b></h200> - Configure interface layout settings and behvaiour <br/>
";
$l['helpPage']['configmain'] = "
<b>Global Settings</b><br/>
<h200><b>Database Settings</b></h200> - Configure database engine, connection settings, tables names if the
default are not used, and the password encryption type in the database.<br/>
<h200><b>Language Settings</b></h200> - Configure interface language.<br/>
<h200><b>Logging Settings</b></h200> - Configure logging rules and facilities <br/>
<h200><b>Interface Settings</b></h200> - Configure interface layout settings and behvaiour <br/>

<b>Sub-Category Configuration</b>
<h200><b>Maintenance </b></h200> - Maintenance options for Testing users connections or terminating their sessions <br/>
<h200><b>Operators</b></h200> - Configure Operators Access Control List (ACL) <br/>
";
$l['helpPage']['configbusiness'] = "
<b>Business Information</b><br/>
<h200><b>Business Contact</b></h200> - set the business contact information (owners, title, address, phone, etc)<br/>
";
$l['helpPage']['configbusinessinfo'] = "";
$l['helpPage']['configmaint'] = "
<b>Maintenance</b><br/>
<h200><b>Test User Connectivity</b></h200> - Send an Access-Request to the RADIUS Server to check if a user credentials are valid<br/>
<h200><b>Disconnect User</b></h200> - Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server
to disconnect a user and terminate his/her session in a given NAS.<br/>
";
$l['helpPage']['configmainttestuser'] = "
<h200><b>Test User Connectivity</b></h200> - Send an Access-Request to the RADIUS Server to check if a user credentials are valid.<br/>
daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes. <br/>
daloRADIUS counts on the radclient binary being available in your \$PATH environment variable, if it is not, please make
corrections to the library/extensions/maintenance_radclient.php file.<br/><br/>

Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and
radclient will retransmit the packets.

In the Advanced tab it is possible to fine-tune the options for the test:<br/>
Timeout - Wait 'timeout' seconds before retrying (may be a floating point number) <br/>
Retries - If timeout, retry sending the packet 'retries' times. <br/>
Count - Send each packet 'count' times <br/>
Requests -  Send 'num' packets from a file in parallel <br/>
";
$l['helpPage']['configmaintdisconnectuser'] = "
<h200><b>Disconnect User</b></h200> - Send a PoD (Packet of Disconnect) or CoA (Change of Authority) packets to the NAS server to disconnect a user and terminate his/her session in a given NAS.<br/>
For terminating a user's session it is required that the NAS support the PoD or CoA packet types, please consult your NAS vendor or
documentation for this. Moreover, it is required to know the NAS ports for PoD or CoA packets, whereas newer NASs use port 3799
while other ones are configured to receive the packet on port 1700.

daloRADIUS uses the radclient binary utility to perform the test and returns the results of the command after it finishes. <br/>
daloRADIUS counts on the radclient binary being available in your \$PATH environment variable, if it is not, please make
corrections to the library/extensions/maintenance_radclient.php file.<br/><br/>

Please note that it may take a while for the test to finish (up to several seconds [10-20 seconds or so]) because of failures and
radclient will retransmit the packets.

In the Advanced tab it is possible to fine-tune the options for the test:<br/>
Timeout - Wait 'timeout' seconds before retrying (may be a floating point number) <br/>
Retries - If timeout, retry sending the packet 'retries' times. <br/>
Count - Send each packet 'count' times <br/>
Requests -  Send 'num' packets from a file in parallel <br/>


";
$l['helpPage']['configoperatorsdel'] = "Primero debe escribir el nombre de usuario del operador para poder borrarlo.";
$l['helpPage']['configoperatorsedit'] = "Editar la informaci&oacute;n del operador";
$l['helpPage']['configoperatorsnew'] = "You may fill below details for a new operator user addition to database";
$l['helpPage']['configoperatorslist'] = "Listado de los operadores registrados";
$l['helpPage']['configoperators'] = "Configuraci&oacute;n de operadores";
$l['helpPage']['configbackup'] = "Ejecutar respaldo";
$l['helpPage']['configbackupcreatebackups'] = "Crear respaldos";
$l['helpPage']['configbackupmanagebackups'] = "Administrar respaldos";


$l['helpPage']['graphmain'] = "
<b>Graphs</b><br/>
<h200><b>Overall Logins/Hits</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of Logins (or 'hits' to the NAS) are displayed in a graph as well as accompanied by a table listing.<br/>
<h200><b>Overall Download Statistics</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of data Downloaded by the client is the value which is being calculated. The graph is accompanied by a table listing<br/>
<h200><b>Overall Upload Statistics</b></h200> - Plots a graphical chart of the usage for a specific user per a given period of time.
The amount of data Upload by the client is the value which is being calculated. The graph is accompanied by a table listing<br/>
<br/>
<h200><b>Alltime Logins/Hits</b></h200> - Plots a graphical chart of the Logins to the server for a given period of time.<br/>
<h200><b>Alltime Traffic Comparison</b></h200> - Plots a graphical chart of the Downloaded and Uploaded statisticse.
";
$l['helpPage']['graphsalltimelogins'] = "An All-Time statistics of Logins to the server based on a distribution over a period of time";
$l['helpPage']['graphsalltimetrafficcompare'] = "An All-Time statistics of Traffic through the server based on a distribution over a period of time.";
$l['helpPage']['graphsoveralldownload'] = "Plots a graphical chart of the Downloaded bytes to the server";
$l['helpPage']['graphsoverallupload'] = "Plots a graphical chart of the Uploaded bytes to the server";
$l['helpPage']['graphsoveralllogins'] = "Plots a graphical chart of the Login attempts to the server";



$l['helpPage']['rephistory'] = "Lista all activity performed on management items and provides information on <br/>
Creation Date, Creation By as well as Updated Date and Update By history fields";
$l['helpPage']['replastconnect'] = "Listado des all login attempts to the RADIUS server, both successful and failed logins";
$l['helpPage']['replogsboot'] = "Monitor Operating System Boot log - equivalent to running the dmesg command.";
$l['helpPage']['replogsdaloradius'] = "Monitor daloRADIUS's Logfile.";
$l['helpPage']['replogsradius'] = "Monitor FreeRADIUS's Logfile.";
$l['helpPage']['replogssystem'] = "Monitor Operating System Logfile.";
$l['helpPage']['replogs'] = "
<b>Logs</b><br/>
<h200><b>daloRADIUS Log</b></h200> - Monitor daloRADIUS's Logfile.<br/>
<h200><b>RADIUS Log</b></h200> - Monitor FreeRADIUS's Logfile - equivalent to /var/log/freeradius/radius.log or /usr/local/var/log/radius/radius.log.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>System Log</b></h200> - Monitor Operating System Logfile - equivalent to /var/log/syslog or /var/log/message on most platform.
Other possible locations for the logfile may take place, if this is the case please adjust the configuration accordingly.<br/>
<h200><b>Boot Log</b></h200> - Monitor Operating System Boot log - equivalent to running the dmesg command.
";
$l['helpPage']['repmain'] = "
<b>General Reports</b><br/>
<h200><b>Online Users</b></h200> - Provides a listing of all users which are
found to be online through the accounting table in the database. The check which is being performed is for users
with no ending time (AcctStopTime) set. It is important to notice that these users may also be of stale sessions
which happens when NASs for some reason fail to send the accounting-stop packets.<br/>
<h200><b>Last Connection Attempts</b></h200> - Provides a listing of all Access-Accept and Access-Reject (accepted and failed) logins
for users. <br/> These are pulled from the database's postauth table which is required to be defined
in FreeRADIUS's config file to actually log these.<br/>
<h200><b>Top User</b></h200> - Provides a listing of the top N users for bandwidth consumption and session time used<br/><br/>
<b>Sub-Category Reports</b><br/>
<h200><b>Logs</b></h200> - Provides access to daloRADIUS logfile, FreeRADIUSs logfile, System's logfile and Boot logfile<br/>
<h200><b>Status</b></h200> - Provides information on server status and RADIUS Components status";
$l['helpPage']['repstatradius'] = "Provides general information about the server itself: CPU Usage, Processes, Uptime, Memory usage, etc.
";
$l['helpPage']['repstatserver'] = "Provides general information about the FreeRADIUS daemon and MySQL Database server";
$l['helpPage']['repstatus'] = "<b>Status</b><br/>
<h200><b>Server Status</b></h200> - Provides general information about the server itself: CPU Usage, Processes, Uptime, Memory usage, etc.<br/>
<h200><b>RADIUS Status</b></h200> - Provides general information about the FreeRADIUS daemon and MySQL Database server";
$l['helpPage']['reptopusers'] = "Records for top users, those which are listed below have gained the highest consumption of session
time or bandwidth usage. Listing users of category: ";
$l['helpPage']['repusername'] = "Records found for user:";
$l['helpPage']['reponline'] = "
The following table lists users who are currently connected to
the system. It is very much possible that there are stale connections,
meaning that users got disconnected but the NAS didn't send or wasn't
able to send a STOP accounting packet to the RADIUS server.
";


$l['helpPage']['mnglistall'] = "Listado de users in database";
$l['helpPage']['mngsearch'] = "Searching for user: ";
$l['helpPage']['mngnew'] = "You may fill below details for new user addition to database<br/>";
$l['helpPage']['mngedit'] = "Editar the user details below.<br/>";
$l['helpPage']['mngdel'] = "To remove a user entry from the database you must provide the username of the account<br/>";
$l['helpPage']['mngbatch'] = "You may fill below details for new user addition to database.<br/>
Note that these settings will apply for all the users that you are creating.<br/>";
$l['helpPage']['mngnewquick'] = "The following user/card is of type prepaid.<br/>
The amount of time specified in Time Credit will be used as the Session-Timeout and Max-All-Session
radius attributes";

// accounting section
$l['helpPage']['acctactive'] = "
    Provides information that would prove useful for tracking Active or Expired users in the database
    in terms of users which have an Expiration attribute or a Max-All-Session attribute.
<br/>
";
$l['helpPage']['acctall'] = "
    Provides full accounting information for all sessions in the database.
<br/>
";
$l['helpPage']['acctdate'] = "
    Provides full accounting information for all sessions between the given 2 dates for a particular user.
<br/>
";
$l['helpPage']['acctipaddress'] = "
    Provides full accounting information for all sessions that originated with a particular IP Address.
<br/>
";
$l['helpPage']['acctmain'] = "
<b>General Accounting</b><br/>
<h200><b>User Accounting</b></h200> -
    Provides full accounting information for all sessions in the database for a particular user.
<br/>
<h200><b>IP Accounting</b></h200> -
    Provides full accounting information for all sessions that originated with a particular IP Address.
<br/>
<h200><b>NAS Accounting</b></h200> -
    Provides full accounting information for all the sessions that the specific NAS IP address has handled.
<br/>
<h200><b>Date Accounting</b></h200> -
    Provides full accounting information for all sessions between the given 2 dates for a particular user.
<br/>
<h200><b>All Accounting Records</b></h200> -
    Provides full accounting information for all sessions in the database.
<br/>
<h200><b>Active Records Accounting</b></h200> -
    Provides information that would prove useful for tracking Active or Expired users in the database
    in terms of users which have an Expiration attribute or a Max-All-Session attribute.
<br/>

<br/>
<b>Sub-Category Accounting</b><br/>
<h200><b>Custom</b></h200> -
    Provides the most flexible custom query to run on the database.
<br/>
<h200><b>Hotspots</b></h200> -
    Provides information on the different managed hotspots, comparison, and other useful information.
<br/>
";
$l['helpPage']['acctnasipaddress'] = "
    Provides full accounting information for all the sessions that the specific NAS IP address has handled.
<br/>
";
$l['helpPage']['acctusername'] = "
    Provides full accounting information for all sessions in the database for a particular user.
<br/>
";
// accounting hotspot section
$l['helpPage']['accthotspotaccounting'] = "
    Provides full accounting information for all sessions which originated from this specific Hotspot.
    This list is computed by listing only those records in the radacct table with the CalledStationId
    field which match a Hotspot's MAC Address entry in the Hotspot's management database.
<br/>
";
$l['helpPage']['accthotspotcompare'] = "
    Provides basic accounting information for comparison between all the active hotspots found in the database.
    Accounting information provided: <br/><br/>
    Hotspot Name - The Hotspot's name <br/>
    Unique Users - Users that have logined only through this hotspot <br/>
    Total Hits - The total logins that were performed from this hotspot (unique and non unique) <br/>
    Average Time - The average time a user spent in this hotspot <br/>
    Total Time - The accumolated spent time of all users in this hotspot <br/>

<br/>
    Provides a graph plot of different comparisons made <br/>
    Graphs: <br/><br/>
    Distribution of Unique users per hotspot <br/>
    Distribution of Hits per hotspot <br/>
    Distribution of Time usage per hotspot <br/>
<br/>
";
$l['helpPage']['accthotspot'] = "
<h200><b>Hotspot Accounting</b></h200> -
    Provides full accounting information for all sessions which originated from this specific Hotspot.
<br/>
<h200><b>Hotspot Comparison</b></h200> -
    Provides basic accounting information for comparison between all the active hotspots found in the database.
    Provides a graph plot of different comparisons made.
<br/>
";
// accounting custom queries section
$l['helpPage']['acctcustom'] = "
<h200><b>Custom</b></h200> -
    Provides the most flexible custom query to run on the database.<br/>
    You may adjust the query to it's max by modifying the settings in the left sidebar.<br/>
<br/>
    <b> Between Dates </b> - Set the beginning and ending date.
<br/>
    <b> Where </b> - Set the field in the database you wish to match (like a key), choose if the value
    to match to should be Equal (=) or it Contains part of the value you search for (like a regex). If you
    choose to use the Contains operator you shouldn't add any wildcards of the common form '*' but rather
    the value you input will automatically be searched in this form: *value* (or in mysql style: %value%).
<br/>
    <b> Query Accounting Fields </b> - You may choose which fields you would like to present in the resulting
    list.
<br/>
    <b> Order By </b> - Choose by which field you would like to order the results and it's type (Ascending
    or descending)
<br/>
";
$l['helpPage']['acctcustomquery'] = "";
$l['helpPage']['acctmaintenance'] = "
<h200><b>Cleanup stale-sessions</b></h200> -
    Stale-sesions may often exist because the NAS was unable to provide an accounting STOP record for the <br/>
    user session, resulting in a stale open session in the accounting records which simulates a fake logged-in user
    record (false positive).
<br/>
<h200><b>Delete accounting records</b></h200> -
    Deletion of accounting records in the database. It may not be wise to perform this or to allow other users
    except for a supervised administrator access to this page.
<br/>
";
$l['helpPage']['acctmaintenancecleanup'] = "";
$l['helpPage']['acctmaintenancedelete'] = "";



$l['helpPage']['giseditmap'] = "
    Edit Map Mode - in this mode you are able to either Add or Delete Hotspots simply by clicking
    on a location of the map or by clicking on a hotspot (respectively).<br/><br/>
    <b> Adding Hotspot </b> - Simply click on a clear location of the map, you will be asked to provide
    the hotspot's name and it's MAC address. These are 2 crucial details later used to identify this hotspot
    in the accounting table. Always provide the correct MAC Address!
<br/><br/>
    <b> Deleting Hotspot </b> - Simply click on a hotspot's icon and you confirm the deletion of it from the
    database.
<br/>
";
$l['helpPage']['gisviewmap'] = "
    View Map Mode - in this mode you are able to browse the Hotspots as they are layed out
    in icons across the maps provided by GoogleMaps service.<br/><br/>

    <b> Clicking a Hotspot </b> -Will provide you with more in-depth detail on the hotspot.
    Such as the contact information for the hotspot, and statistics details.
<br/>
";
$l['helpPage']['gismain'] = "
<b> General Information </b>
GIS Mapping provides visual mappings of the hotspot location across the world's map using Google Maps API. <br/>
In the Management page you are able to add new hotspot entries to the database where there is also a field
called Geolocation, this is the numeric value that the Google Maps API uses in order to pin-point the exact
location of that hotspot on the map.<br/><br/>

<h200><b>2 Modes of Operation are provided:</b></h200>
One is the <b>View MAP</b> mode which enables 'surfing' through the world map
and view the current locations of the hotspots in the database and another one - <b>Edit MAP</b> - which is the mode
that one can use in order to create hotspot's visually by simply left-clicking on the map or removing
existing hotspot entries by left-clicking on existing hotspot flags.<br/><br/>

Another important issue is that each computer on the network requires a unique Registration code which you
can obtain from Google Maps API page by providing the complete web address to the hosted directory of
daloRADIUS application on your server. Once you have obtained that code from Google, simply paste it in the
Registration box and click the 'Register code' button to write it.
Then you may be able to use Google Maps services. <br/><br/>";

/* ********************************************************************************** */



$l['messages']['noCheckAttributesForUser'] = "This user has no check attributes associated with it";
$l['messages']['noReplyAttributesForUser'] = "This user has no reply attributes associated with it";

$l['messages']['noCheckAttributesForGroup'] = "This group has no check attributes associated with it";
$l['messages']['noReplyAttributesForGroup'] = "This group has no reply attributes associated with it";

$l['messages']['nogroupdefinedforuser'] = "This user has no Groups associated with it";
$l['messages']['wouldyouliketocreategroup'] = "Would you like to create one?";


$l['messages']['missingratetype'] = "error: missing rate type to delete";
$l['messages']['missingtype'] = "error: missing type";
$l['messages']['missingcardbank'] = "error: missing cardbank";
$l['messages']['missingrate'] = "error: missing rate";
$l['messages']['success'] = "success";
$l['messages']['gisedit1'] = "Welcome, you are currently in Edit mode";
$l['messages']['gisedit2'] = "Remove current marker from map and database?";
$l['messages']['gisedit3'] = "Please enter name of HotSpot";
$l['messages']['gisedit4'] = "Add current marker to database?";
$l['messages']['gisedit5'] = "Please enter name of HotSpot";
$l['messages']['gisedit6'] = "Please enter the MAC Address of the Hotspot";

$l['messages']['gismain1'] = "Successfully updated GoogleMaps API Registration code";
$l['messages']['gismain2'] = "error: could not open the file for writing:";
$l['messages']['gismain3'] = "Verifique los permisos del archivo. El archivo debe poder ser escrito por el usuario/grupo del servidor web";
$l['messages']['gisviewwelcome'] = "Bienvenido a los Mapas Visuales de Enginx";

$l['messages']['loginerror'] = "<br/><br/>either of the following:<br/>
1. bad username/password<br/>
2. an administrator is already logged-in (only one instance is allowed) <br/>
3. there appears to be more than one 'administrator' user in the database <br/>
";

$l['buttons']['savesettings'] = "Guardar valores";
$l['buttons']['apply'] = "Aplicar";

$l['menu']['Home'] = "Inicio";
$l['menu']['Managment'] = "Gesti&oacute;n";
$l['menu']['Reports'] = "Reportes";
$l['menu']['Accounting'] = "Conteo";
$l['menu']['Billing'] = "Cobros";
$l['menu']['Gis'] = "GIS";
$l['menu']['Graphs'] = "Gr&aacute;ficos";
$l['menu']['Config'] = "Configuraci&oacute;n";
$l['menu']['Help'] = "Ayuda";

// TODO translation needed.
// once translated, delete these comment lines.
$l['submenu']['General'] = "General";
$l['submenu']['Reporting'] = "Reporting";
$l['submenu']['Maintenance'] = "Maintenance";
$l['submenu']['Operators'] = "Operators";
$l['submenu']['Backup'] = "Backup";
$l['submenu']['Logs'] = "Logs";
$l['submenu']['Status'] = "Status";
$l['submenu']['Batch Users'] = "Batch Users";
$l['submenu']['Dashboard'] = "Dashboard";
$l['submenu']['Users'] = "Users";
$l['submenu']['Hotspots'] = "Hotspots";
$l['submenu']['Nas'] = "Nas";
$l['submenu']['User-Groups'] = "User-Groups";
$l['submenu']['Profiles'] = "Profiles";
$l['submenu']['HuntGroups'] = "HuntGroups";
$l['submenu']['Attributes'] = "Attributes";
$l['submenu']['Realm/Proxy'] = "Realm/Proxy";
$l['submenu']['IP-Pool'] = "IP-Pool";
$l['submenu']['POS'] = "POS";
$l['submenu']['Plans'] = "Plans";
$l['submenu']['Rates'] = "Rates";
$l['submenu']['Merchant-Transactions'] = "Merchant-Transactions";
$l['submenu']['Billing-History'] = "Billing-History";
$l['submenu']['Invoices'] = "Invoices";
$l['submenu']['Payments'] = "Payments";
$l['submenu']['Custom'] = "Custom";
$l['submenu']['Hotspot'] = "Hotspot";

?>
