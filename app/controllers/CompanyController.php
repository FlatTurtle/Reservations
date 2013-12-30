<?php
/**
 * Author: Nik Torfs
 */
class CompanyController extends BaseController
{

    function getCompanies(Cluster $cluster){
        $companies = Company::where('cluster_id', '=', $cluster->id)->get();

        // decode company domains
        foreach( $companies as $company){
            $company->domains = json_decode($company->domains);
        }

        return $companies;
    }

    function getCompany(Cluster $cluster, $id){
        $company = Company::byId($cluster->id, $id)->first();
        if(isset($company)){
            $company->domains = json_decode($company->domains);
            return $company;
        }else{
            return $this->_sendErrorMessage(404, "Company.NotFound", "Company not found");
        }
    }

    function createCompany(Cluster $cluster, $name){
        if (!strcmp($cluster->clustername, Auth::user()->clustername) || Auth::user()->isAdmin()) {

            // todo pull up method and put it in basecontroller
            $content = Request::instance()->getContent();
            if (empty($content)){
                return $this->_sendErrorMessage(400, "Payload.Null", "Received payload is empty.");
            }
            if (Input::json() == null){
                return $this->_sendErrorMessage(400, "Payload.Invalid", "Received payload is invalid.");
            }


            $validator = Validator::make(
                Input::json()->all(),
                array(
                    "name" => "required",
                    "logo_url" => "required|url"
                )
            );

            if (!$validator->fails()){
                $company_name = Input::json()->get('name');
                $company = Company::byName($cluster->id, $company_name)->first();

                // if no domains are included make it an empty array
                $domains = Input::json()->get('domains');
                if(!isset($domains)){
                    $domains = array();
                }

                //
                $data = array(
                    "name" => $company_name,
                    "logo_url" => Input::json()->get('logo_url'),
                    "domains" => json_encode($domains)
                );


                if(isset($company)){
                    // if the company already exists -> update
                    $company->fill($data);

                    if($company->save()){
                        return $company;
                    }
                }else{
                    //otherwise we create a new one
                    $data["cluster_id"] = $cluster->id;
                    return Company::create($data);
                }

            }else{
                return $this->_sendValidationErrorMessage($validator);
            }
        } else {
            return $this->_sendErrorMessage(403, "WriteAccessForbiden", "You can't create things on behalf of another user.");
        }
    }

    function deleteCompany(Cluster $cluster, $name){
        if (!strcmp($cluster->clustername, Auth::user()->clustername) || Auth::user()->isAdmin()) {

            $company = Company::byName($cluster->id, $name)->first();

            if ($company != null)
                if($company->delete())
                    return Response::json(
                        array(
                            'success' => true,
                            'message' => 'Company successfully deleted'
                        )
                    );
                else
                    return $this->_sendErrorMessage(500, "Company.Unknown", "An error occured while deleting the company.");
            else
                return $this->_sendErrorMessage(404, "Company.NotFound", "Company not found.");

        } else {
            return $this->_sendErrorMessage(403, "DeleteAccessForbiden", "You can't delete companies from another cluster.");
        }
    }
}
