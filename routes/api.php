<?php

use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::resource('countries','CountryController')->middleware('jwt.auth');
Route::resource('states','StateController');
Route::resource('cities','CityController');
Route::resource('villages','VillageController');
Route::resource('districts','DistrictController');
Route::resource('blocks','BlockController');
Route::get('user/test','CustomAuthenticationController@testToken')->middleware('jwt.auth');
Route::post('logout','CustomAuthenticationController@logout')->middleware('jwt.auth');
Route::post('/user/{id}/statuschange','UserController@statusChange');
Route::get('getdeactivateduser','UserController@getDeactivatedUser');
Route::resource('users','UserController');
Route::post('user/{user_id}','UserController@updateUser');
Route::post('/login','CustomAuthenticationController@login');
Route::resource('drishtee','DrishteeMitraController');
Route::post('dm/login','DrishteeMitraController@login');
Route::post('dm/logout','DrishteeMitraController@logout')->middleware('jwt.auth');
Route::get('checkmobile','DrishteeMitraController@checkMobile');
Route::post('dm/resendotp','DrishteeMitraController@resendOtp');
Route::get('refreshtoken','DrishteeMitraController@refreshToken');

Route::Post('drishtee/{id}/status/{transfer_dm_id}','DrishteeMitraController@statusChange');

Route::resource('units','UnitController');
Route::resource('productcategory','ProductCategoryController');
Route::resource('geography','GeographyController');
Route::post('productcategory/{id}/uploadicon','ProductCategoryController@uploadImage');
Route::patch('productcategory/{id}/statuschange','ProductCategoryController@deactivatePC');
Route::resource('servicecategory','ServiceCategoryController');
Route::post('servicecategory/{id}/uploadicon','ServiceCategoryController@uploadImage');
Route::patch('servicecategory/{id}/statuschange','ServiceCategoryController@changeActiveStatus');
Route::get('test','UserController@test')->middleware('jwt.auth');
Route::post('importstate','StateController@importState');
Route::resource('products','ProductController');

Route::get('productsexport','ProductController@exportProducts');
Route::get('productsexport/{filepath}','ProductController@downloadExportProducts');
Route::get('productsimport/samplefile','ProductController@importSamplefile');
Route::get('product/samplefile/{filepath}','ProductController@downloadImportSamplefile');
Route::post('productsimport','ProductController@importProducts')->middleware('jwt.auth');

Route::get('productgeography/samplefile','ProductGeographyAvailabilityController@importSamplefile');
Route::get('productgeography/samplefile/{filepath}','ProductGeographyAvailabilityController@downloadImportSamplefile');
Route::post('productgeography/import','ProductGeographyAvailabilityController@productgeographyImport');

Route::post('drishtee/{id}/uploadimage','DrishteeMitraController@uploadImage');
Route::patch('drishtee/{id}/onboardcomplete','DrishteeMitraController@onBoardComplete');
Route::post('product/{id}/productalias','ProductController@productAlias');
Route::post('service/{id}/servicealias','ServiceController@serviceAlias');

Route::post('filterproducts','ProductController@filteredProducts');
Route::post('filterservices','ServiceController@filteredServices');
Route::post('filteredpersons','PersonController@filteredPersons');

Route::post('filterdrishtee','DrishteeMitraController@filterDrishtee');
Route::post('filterbarter','BarterController@filterBarter');
Route::post('drishtee/{id}/device','DrishteeMitraController@saveDeviceDetail');
Route::resource('services','ServiceController');

Route::get('servicesexport','ServiceController@exportServices');
Route::get('servicesexport/{filepath}','ServiceController@downloadExportServices');
Route::get('servicesimport/samplefile','ServiceController@importSamplefile');
Route::get('service/samplefile/{filepath}','ServiceController@downloadImportSamplefile');
Route::post('servicesimport','ServiceController@importServices')->middleware('jwt.auth');

Route::post('marginpercentage','MarginPercentageController@store');
Route::get('getmarginpercentage','MarginPercentageController@index');

Route::post('product/{id}/uploadimage','ProductController@uploadImage');
Route::post('service/{id}/uploadimage','ServiceController@uploadImage');

Route::resource('persons','PersonController');
Route::patch('person/{id}/personlocationsave','PersonController@personLocationSave');
Route::patch('person/{id}/personpersonaldetailsave','PersonController@personPersonalSave');
Route::patch('person/{id}/accountdetailsave','PersonController@personBankAccountDetailSave');
Route::patch('person/{id}/kycdetailsave','PersonController@personKycDetailsSave');
Route::patch('person/{id}/educationsave','PersonController@personEducationSave');
Route::patch('person/{id}/infrastructuresave','PersonController@personInfrastructureSave');
Route::patch('person/{id}/incomesave','PersonController@personIncomeSave');
Route::patch('person/{id}/skillsave','PersonController@personSkillSave');
Route::patch('person/{id}/trainingsave','PersonController@personTrainingSave');
Route::patch('person/{id}/livehoodsave','PersonController@personLiveHoodEngagementSave');
Route::patch('person/{id}/workexperiencesave','PersonController@personWorkingExperienceSave');
Route::patch('person/{person_id}/workexperienceupdate/{experience_id}','PersonController@personWorkingExperienceUpdate');
Route::patch('person/{person_id}/trainingupdate/{training_id}','PersonController@personTrainingUpdate');
Route::patch('person/{person_id}/skillupdate/{skill_id}','PersonController@personSkillUpdate');
Route::delete('person/{person_id}/workexperiencedelete/{experience_id}','PersonController@deleteWorkExperience');
Route::delete('person/{person_id}/trainingdelete/{training_id}','PersonController@deleteTraining');
Route::delete('person/{person_id}/skilldelete/{skill_id}','PersonController@deleteSkill');

Route::get('personexport/{person_id}','PersonController@exportPerson');
Route::get('person/export/{filename}','PersonController@downloadExportPerson');
Route::resource('serviceratelist','ServiceRateListController');
Route::post('serviceratelistupdate','ServiceRateListController@updateAll');

Route::get('serviceratelistexport','ServiceRateListController@exportServiceRateList');
Route::get('serviceratelistexport/{filepath}','ServiceRateListController@downloadExportServiceRateList');

Route::resource('personproduct','PersonProductController');
Route::resource('personservice','PersonServiceController');

Route::resource('productgeographyavailability','ProductGeographyAvailabilityController');
Route::post('personkyc/{id}/uploadimage','PersonController@uploadKycImage');
Route::post('personpersonal/{id}/uploadimage','PersonController@uploadImage');
Route::resource('barters','BarterController');
Route::delete('barterhaveproduct/{id}','BarterController@barterHaveProductDelete');
Route::delete('barterhaveservice/{id}','BarterController@barterHaveServiceDelete');
Route::delete('barterhavelp/{id}','BarterController@barterHaveLpDelete');
Route::delete('barterneedproduct/{id}','BarterController@barterNeedProductDelete');
Route::delete('barterneedservice/{id}','BarterController@barterNeedServiceDelete');
Route::delete('barterneedlp/{id}','BarterController@barterNeedLpDelete');

Route::get('dm/{id}/persons','PersonController@getDmPersons');//
Route::get('person/{id}/products','PersonProductController@personProductGet');
Route::get('person/{id}/services','PersonServiceController@personServiceGet');
Route::get('person/{id}/lp','PersonController@personLpGet');


// Routes required for Tejas Products
// 
// 0. Get all Tejas Products from Global DB
Route::get('product/tejas', 'ProductController@getAllTejasProducts');
// 






// 1. Get Person's Tejas Products
// Route::get('person/{id}/tejas', 'PersonProductController@getPersonTejasProducts');
// 




/// Buy from person
Route::get('dm/{dm_id}/buyfrompersons/requests', 'TejasRequestBuyFromPeopleController@buyFromPersonRequests');
Route::get('dm/{dm_id}/buyfrompersons/request/{request_id}', 'TejasRequestBuyFromPeopleController@buyFromPersonSingleRequest');
Route::get('dm/{dm_id}/buyfrompersons', 'PersonProductController@getPersonList'); /// get person list (those person have tejasproduct)
Route::get('dm/{dm_id}/buyfrompersons/{person_id}', 'PersonProductController@getPersonProductList'); //get product list of one person
Route::get('dm/{dm_id}/buyfrompersons/{person_id}/product/{product_id}', 'PersonProductController@getTejasProduct'); // get one produt of person_id
Route::post('dm/{dm_id}/buyfrompersons/{person_id}/products', 'TejasRequestBuyFromPeopleController@buyFromPersonRequest'); //buy request



//Sell to person
Route::get('dm/{dm_id}/selltopersons', 'PersonProductController@getSellPersonList'); //get person list
Route::get('dm/{dm_id}/selltopersons/products', 'PersonProductController@getProductList'); /// get product list of dm_id user
Route::post('dm/{dm_id}/selltopersons/{person_id}/products', 'TejasRequestSellToPeopleController@sellToPersonRequest'); //buy request
Route::get('dm/{dm_id}/selltopersons/requests', 'TejasRequestSellToPeopleController@sellToPersonRequests');
Route::get('dm/{dm_id}/selltopersons/request/{request_id}', 'TejasRequestSellToPeopleController@sellToPersonSingleRequest');


/// Sell to drishtee
Route::get('dm/{dm_id}/selltodrishtee/products', 'PersonProductController@getTejasProducts'); /// get all tejas product list
Route::get('dm/{dm_id}/selltodrishtee/person/{person_id}/product/{product_id}', 'PersonProductController@getProduct'); /// get one produt of person_id
Route::post('dm/{dm_id}/selltodrishtee/products', 'TejasProductSellRequestController@requestStore');
 //save sell request


Route::get('dm/{dm_id}/sellrequests', 'TejasProductSellRequestController@getSellRequestList'); //Sell Request List related to dm_id
Route::get('dm/{requester_id}/sellrequest/{request_id}/cancel', 'TejasProductSellRequestController@cancelSellRequest');


//api for admin panel sell to drishtee
Route::get('dm/sellrequests', 'TejasProductSellRequestController@getSellRequestListAdmin'); //Sell Request List
Route::get('dm/sellrequest/{request_id}', 'TejasProductSellRequestController@getSingleSellRequest');
Route::get('dm/{requester_id}/sellrequest/{request_id}', 'TejasProductSellRequestController@getSellRequest'); //Sell Request List related to requester_id and request_id
Route::post('dm/{commentor_id}/sellrequest/{request_id}/comment', 'TejasProductSellRequestController@addCommentRequestAdmin');

Route::post('dm/sellrequest/{request_id}/status', 'TejasProductSellRequestController@updateStatusRequestAdmin')->middleware('jwt.auth');






//Buy from drishtee
Route::get('dm/{dm_id}/buyfromdrishtee/product/{product_id}', 'PersonProductController@getDrishteeProduct'); //get single product related to product_id
Route::post('dm/{dm_id}/buyfromdrishtee/products', 'TejasProductBuyRequestController@requestStore');


Route::get('dm/{dm_id}/buyrequests', 'TejasProductBuyRequestController@getBuyRequestList'); //Buy Request List related to dm_id
Route::get('dm/{requester_id}/buyrequest/{request_id}/cancel', 'TejasProductBuyRequestController@cancelBuyRequest');


//api for admin panel sell to drishtee
Route::get('dm/buyrequests', 'TejasProductBuyRequestController@getBuyRequestListAdmin'); //Buy Request List
Route::get('dm/buyrequest/{request_id}', 'TejasProductBuyRequestController@getSingleBuyRequest');
Route::get('dm/{requester_id}/buyrequest/{request_id}', 'TejasProductBuyRequestController@getBuyRequest'); //Buy Request List related to requester_id and request_id
Route::post('dm/{commentor_id}/buyrequest/{request_id}/comment', 'TejasProductBuyRequestController@addCommentRequestAdmin');

Route::post('dm/buyrequest/{request_id}/status', 'TejasProductBuyRequestController@updateStatusRequestAdmin')->middleware('jwt.auth');







// 2. Create a new Tejas Product in Person's Product Inventory

// Route::post('person/{id}/tejas', 'PersonProductController@createPersonTejasProduct');
Route::get('dm/{id}/barter','BarterController@getAllBarterOfDm');
Route::get('dm/{id}/activebarter','BarterController@getAllActiveBarter');
Route::get('person/{id}/barter','BarterController@getPersonBarter');
Route::get('dm/barters/{id}/matches/activebarter','BarterController@getAllProductServiceForBarter');
Route::get('dm/barters/{id}/matches/activebartertest','BarterController@getAllProductServiceForBarterTest');

Route::post('dm/barters/{id}/matches/add/local','BarterMatchController@store');
Route::delete('bartermatch/{id}','BarterMatchController@destroy');
Route::get('bartermatchget/{id}','BarterController@bartetMatchGetSingle');

Route::get('lpcalculatebystateid','ServiceRateListController@calculateLp');
Route::get('dm/barters/{id}/matche/{match_id}','BarterMatchController@checkBarterMatchComplete');
Route::patch('dm/barters/{id}/complete','BarterMatchController@barterMarkComplete');
Route::patch('dm/barter/{id}/lock','BarterMatchController@barterMatchConfirm');
Route::patch('dm/barter/{id}/unlock','BarterMatchController@barterUnlock');


Route::resource('lprequestfromadmin','LpRequestFromAdminController');
Route::post('lprequestfromadmin/{id}/statuschange','LpRequestFromAdminController@statusChange');
Route::get('getallpendingrequest','LpRequestFromAdminController@getAllPendingRequest');
Route::get('getallrequest/{id}','LpRequestFromAdminController@getAllRequest');
Route::get('getbarterconfirmation/{id}','BarterController@getBarterConfirmation');
Route::patch('barterconfirm/{id}','BarterMatchController@confirmPersonStatus');
Route::resource('dmmarginpercentage','DmMarginPercentageController');
Route::get('getbartertransaction/{id}','BarterController@getBarterTransactions');
Route::get('getusertransaction/{id}','UserController@getUserLedgerTransaction');
Route::resource('bartermatchtest','BarterMatchController');
Route::get('bartermatchconfirm/{id}/statuschange','BarterMatchController@barterMatchConfirmByPerson');

Route::resource('disputes','DisputeController');
Route::get('dm/{id}/getalldispute','DisputeController@getDmAllDispute');

Route::get('getdisputeforadmin','DisputeController@getDisputeForAdmin')->middleware('jwt.auth');
Route::get('getdisputeforadmin/{dispute_id}','DisputeController@getSingleDisputeForAdmin');

Route::post('dispute/{id}/statuschange','DisputeController@statusChange');

Route::post('dispute/{id}/comment','DisputeController@comment');

Route::get('person/{id}/gettransaction','PersonController@getPersonLedgerTransaction');
Route::get('dm/{dm_id}/gettransaction','DrishteeMitraController@getDMLedgerTransaction');
Route::get('dm/{dm_id}/exporttransaction','DrishteeMitraController@exportDMLedgerTransaction');


Route::get('testsms','PersonController@sendSMS');
Route::post('dmtransfer/{dm_id}/{transfer_id}','DrishteeMitraController@dmTransfer');



Route::post('dm/{dm_id}/personban/{person_id}','PersonBanController@personBanRequest');
Route::get('personbanrequests','PersonBanController@getPersonBanRequests');
Route::post('personbanrequest/{request_id}','PersonBanController@approvePersonBanRequests')->middleware('jwt.auth');
Route::post('person/{person_id}/status','PersonController@statusChange');

Route::post('person/{person_id}/ledgers/filter','PersonController@ledgerfilter');
Route::post('user/{user_id}/ledgers/filter','UserController@ledgerfilter');
Route::post('dm/{dm_id}/ledgers/filter','DrishteeMitraController@ledgerfilter');

Route::get('dm/{dm_id}/notifications', 'NotificationController@getNotificationList');
Route::get('dm/{dm_id}/notification/{notification_id}', 'NotificationController@getNotification');


Route::post('creategeography','GeographyController@createGeography');

Route::post('createdm','DrishteeMitraController@createDrishteeMitra');

// Route::get('dashboardstats','GeographyController@getStats');

Route::get('drishtee/lp/details', 'PersonController@drishteeLPDetails');
Route::get('drishtee/person/details', 'PersonController@drishteePersonDetails');
Route::get('drishtee/transaction/details', 'PersonController@drishteeTransactionDetails');

Route::get('dashboard','DrishteeMitraController@dashboard');

Route::get('dashboard/tejasproducts/export','DrishteeMitraController@dashboardTejasProductsExport');
Route::get('dashboard/avgservices/export','DrishteeMitraController@dashboardAvgServicesExport');
Route::get('dashboard/avgproduct/export','DrishteeMitraController@dashboardAvgProductsExport');

Route::get('dashboard/avgpeoplemitra/export','DrishteeMitraController@dashboardAvgPeopleMitraExport');
Route::get('dashboard/mitrawithnopeople/export','DrishteeMitraController@dashboardMitraNoPeople');
Route::get('dashboard/peoplewithnoproduct/export','DrishteeMitraController@dashboardPeopleWithNoProduct');


Route::get('report/geography/export','DrishteeMitraController@reportGeographyExport');
Route::get('report/product/export','DrishteeMitraController@reportProductsExport');
Route::get('report/service/export','DrishteeMitraController@reportServicesExport');
Route::get('report/geographywiseledger/export','DrishteeMitraController@reportGeographyWiseLedgerExport');

Route::get('report/personwithlp','PersonController@exportPersonWithLp');
Route::get('report/personwithnolp','PersonController@exportPersonWithNoLp');
Route::get('report/ledger/export','DrishteeMitraController@reportLedgerExport');
Route::get('report/barter/export','DrishteeMitraController@reportBarterExport');
Route::post('createadminfromexternalurl','UserController@storeUserFromExternal');
Route::get('getdistrictbystate/{state_id}','DistrictController@getDistrictByStateId');
Route::post('deletegeography/{id}','GeographyController@deleteGeography');
Route::post('deletecountry/{id}','CountryController@deleteCountry');
Route::post('deletestate/{id}', 'StateController@deleteState');
Route::post('deletedistrict/{id}', 'DistrictController@deleteDistrict');
Route::post('deleteblock/{id}', 'BlockController@deleteBlock');
Route::post('deletecity/{id}', 'CityController@deleteCity');
Route::post('deletevillage/{id}', 'VillageController@deleteVillage');
Route::post('deleteproduct/{id}','ProductController@deleteProduct');
Route::post('deleteuser/{id}','UserController@deleteUser');
Route::post('deleteservice/{id}','ServiceController@deleteService');
Route::post('deleteperson/{id}', 'PersonController@deletePerson');
Route::post('deletedm/{id}', 'DrishteeMitraController@deleteDm');
Route::get('envurl','CityController@envUrl');
Route::post('updategeographyexternal','GeographyController@updateGeographyExternal');
Route::post('exportallperson','PersonController@downloadAllPersons');


// Route::get('totalregisterproducercount','PersonController@personCount');
Route::get('producercountandlpcount','PersonController@personCountLpCount');
Route::get('totallp','PersonController@totalLp');
Route::get('allvatika','PersonController@allVatika');
Route::get('allpersonaccount','PersonController@peopleAccountDetails');
Route::get('allpersonaccountingeography','PersonController@personDetailsByVatikaName');
Route::get('productcount','ProductController@productCount');
Route::get('bartermatch/{id}','BarterController@getBarter');
Route::resource('userproducts','UserProductController');
Route::post('userproducts/report','UserProductController@report');
Route::get('testpipeline','UserProductController@test'); // comment added

Route::post('deletemitraexternal','DrishteeMitraController@deleteMitraExternal');
Route::post('deleteuserexternal','UserController@deleteUserExternal');
Route::post('barterdeleteforce/{id}','BarterController@deleteBarterForce')->middleware('jwt.auth');
Route::post('userproductslog','UserProductController@sellProduct');

Route::get('/getpersonfirst','DrishteeMitraController@personAddDate');
Route::get('personadddate/{filepath}','DrishteeMitraController@downloadExportPersonAdd');
Route::resource('vaccinations','VaccinationController');
Route::post('filtervaccinations','VaccinationController@filterVaccination');
Route::post('vaccinations/{id}/uploadcertificate','VaccinationController@uploadPDF');
Route::post('vaccinations/{id}/uploadcertificatedose2','VaccinationController@uploadPDFDose2');

Route::get('vaccinationstats','VaccinationController@vaccinationStats');
Route::get('vaccinationstatsdmwise/{dm_id}','VaccinationController@vaccinationStatsDmWise');
Route::resource('dashboardstats','DashboardStatsController');
Route::get('getvaccinename','VaccinationController@getVaccineName');
Route::get('personaddreport','PersonController@personRegistrationReport');
Route::get('dmreportmonthwise/{filepath}','PersonController@dmReportMonthWise');
Route::post('importcsp','DrishteeMitraController@importCsp');
Route::post('vaccination/statuschange/{id}','VaccinationController@statusChange');
Route::get('commisionreport','DrishteeMitraController@comissionMonthlyReport');
Route::get('comissionreportmonthwise/{filepath}','DrishteeMitraController@comissionReportMonthWise');

Route::post('filterusers','UserController@filterUser');
Route::get('geoassign','GeographyController@geographyAddInSuperAdmin');
Route::post('filtergeography','GeographyController@filterGeography');

Route::get('getdmvaccinations/{dm_id}','VaccinationController@getVaccinationByDm');
Route::get('personupdatescript','PersonController@updateScript');
Route::post('dmmobileupdate','DrishteeMitraController@mobileUpdate');

Route::get('getvaccinestats','VaccinationController@getpersonsByVaccineDose');
Route::get('getvaccinedm/{flag}','VaccinationController@getVaccinePeopleByDMFlage');

Route::get('getvaccinelist','VaccinationController@getVaccinationList');
Route::get('vaccination/list/report','DrishteeMitraController@vaccinationListReport');
Route::get('dateassign','VaccinationController@dateAssign');
Route::get('getbankdetails','PersonController@updatebankdetails');
Route::get('cspconvert','DrishteeMitraController@cspconvert');
Route::get('updatebankdetails','PersonController@updatebankdetails');
Route::get('producerlist','PersonController@producerList');
Route::post('uploadpersoncertificate/{id}','PersonController@uploadVaccinationFile');
Route::get('allmitra','DrishteeMitraController@allMitra');
Route::get('correctledger','DrishteeMitraController@correctLedgerId');
Route::get('correctledgerbal','DrishteeMitraController@ledgerCorrect');
Route::get('apifortarun','DrishteeMitraController@dataForTarun');
Route::get('udyogidata','DrishteeMitraController@getUdyogiVaccinationData');
Route::get('vaccinationstatsexternal','VaccinationController@vaccinationStatsExternal');
Route::get('reportkbsingh','PersonController@reportFromFirstJantoThirdMarch');
Route::get('barterapiexternal','BarterController@barterApiForTarun');
