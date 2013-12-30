<?php
/**
 * Author: Nik Torfs
 */
class Company extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company';

    protected $hidden = array("created_at", "updated_at");

    protected $fillable = array('name', 'domains', 'logo_url', 'cluster_id');

    public function cluster() {
        return $this->belongsTo('Cluster', 'cluster_id');
    }

    /**
     * Get the query for retrieving a company linked to a certain cluster by id
     *
     * syntax: Company::byId(cluster_id, company_id)
     *
     * @param $query
     * @param $cluster_id
     * @param $company_id
     * @return mixed
     */
    public function scopeById($query, $cluster_id, $company_id){
        return $query
            ->where('cluster_id', '=', $cluster_id)
            ->where('id', '=', $company_id);
    }

    /**
     * Get the query for retrieving a company linked to a certain cluster by name
     *
     * syntax: Company::byName(cluster_id, company_name)
     *
     * @param $query
     * @param $cluster_id
     * @param $company_name
     * @return mixed
     */
    public function scopeByName($query, $cluster_id, $company_name){
        return $query
            ->where('cluster_id', '=', $cluster_id)
            ->where('name', '=', $company_name);
    }
}
