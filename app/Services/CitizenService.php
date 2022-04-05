<?php

namespace App\Services;

use App\Models\Citizen;
use App\Repositories\CitizenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Roles;


class CitizenService
{

    /**
     * @var CitizenRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new CitizenRepository();
    }

    public function getAll(Request $request)
    {
        $user = Auth::user();
        $query = Citizen::query()
            ->with('region:id,name_cyrl')
            ->with('city')
            ->with('social_areas');

        if ($user->role_id == Roles::REGION_ID){
            $query->where(['region_id' => $user->region_id]);
        }
        if ($user->role_id == Roles::CITY_ID){
            $query->where(['city_id' => $user->city_id]);
        }

        if (!empty($request->all()['region_id'])){
            $query->where(['region_id' => $request->all()['region_id']]);
        }
        if (!empty($request->all()['city_id'])){
            $query->where(['city_id' => $request->all()['city_id']]);
        }
        if (!empty($request->all()['social_areas_id'])){
            $query->where(['social_areas_id' => $request->all()['social_areas_id']]);
        }
        if (!empty($request->all()['last_name'])){
            $query->where('citizens.last_name', 'like', '%'. $request->all()['last_name'].'%');
        }
        if (!empty($request->all()['first_name'])){
            $query->where('citizens.first_name', 'like', '%'. $request->all()['first_name'].'%');
        }
        if (!empty($request->all()['fathers_name'])){
            $query->where('citizens.fathers_name', 'like', '%'. $request->all()['fathers_name'].'%');
        }
        if (!empty($request->all()['passport'])){
            $query->where('citizens.passport', 'like', '%'. $request->all()['passport'].'%');
        }

//        $citizens = $query->paginate(2)->toArray();
//
//        unset($citizens['first_page_url']);
//        unset($citizens['last_page_url']);
//        unset($citizens['next_page_url']);
//        unset($citizens['prev_page_url']);
//        unset($citizens['path']);
//        return $citizens;

        return $query->paginate(30);
        return [
            'current_page' => $request->page ?? 1,
            'per_page' => $request->limit,
            'data' =>$query->get(),
            'total' => $query->count() < $request->limit ? $query->count() : -1
        ];


    }
    public function store($request)
    {

        $user = Auth::user();

        $validator = $this->repository->toValidate($request->all());
        $msg = "";

//        $citizen = $this->repository->store($request);

//        return response()->successJson(['citizen' => $citizen]);

        if (!$validator->fails()){


            if ($user->role_id == Roles::ADMIN_ID){
                return response()->errorJson('Рухсат мавжуд емас', 101);
            }
            if ($user->role_id == Roles::REGION_ID){
                return response()->errorJson('Рухсат мавжуд емас', 101);
            }
            if ($user->role_id == Roles::CITY_ID){
                $citizen = $this->repository->store($request);
                return response()->successJson(['citizen' => $citizen]);
            }
        }
        else{
            $errors = $validator->failed();
            if(empty($errors)) {
                $msg = "Соҳалар нотўғри киритилди";
            }
            return response()->errorJson($msg, 400, $errors);
        }


    }

    public function show($id)
    {
        $user = Auth::user();
        $query = Citizen::query();
        $query->where(['id' => $id])
            ->with('region:id,name_cyrl')
            ->with('city')
            ->with('social_areas:id,name_cyrl');;

        if (empty($query->first())){
            return response()->errorJson('Бундай ид ли фойдаланувчи мавжуд емас', 409);
        }
        if ($user->role_id == Roles::ADMIN_ID){
            return $query->first();
        }
        if ($user->role_id == Roles::REGION_ID){
            $query->where(['region_id' => $user->region_id]);
            if (empty($query->first())){
                return response()->errorJson('Рухсат мавжуд емас', 101);
            }
            return $query->first();
        }
        if ($user->role_id == Roles::CITY_ID){
            $query->where(['city_id' => $user->city_id]);
            if (empty($query->first())){
                return response()->errorJson('Рухсат мавжуд емас', 101);
            }
            return $query->first();
        }
    }

    public function update($request, $id){

        $msg = "";
        $validator = $this->repository->toValidate($request->all());

        if (!$validator->fails()) {
                $citizen = $this->repository->update($request, $id);
                return  ['status' => 200, 'citizen' => $citizen];
        } else {
            $errors = $validator->failed();
            if(empty($errors)) {
                $msg = "Соҳалар нотўғри киритилди";
            }
            return ['msg' => $msg, 'status' => 422, 'error' => $errors];
        }
    }


//    {
//        $type = \request('type', 'young');
//        $user = $this->repository->guard()->user();
//        $passports = ['AA0215962'];
//        if(($user->id == 262) && !in_array($request->passport, $passports)) {
//            return response()->errorJson('Маълумот топилмади!', 404);
//        }
//
//
//        $citizen = $this->repository->getQuery()->where('passport', $request->passport)->first();
//        $citizenActionCheck = $citizen->citizenAction;
//        if($citizenActionCheck && !$citizenActionCheck->survey_create) {
//            return response()->errorJson('Фуқарони ёшлар (аёллар) дафтарига рўйҳатга олиш учун фуқаро бандлиги таъминланмаган ва ижтимоий тоифаси аниқланмаган бўлиши керак!', 409);
//        }
//
//        if($citizen && ($user->city_id != $citizen->city_id)) {
//            return response()->errorJson('Маълумот топилмади!', 404);
//        }
//        if($citizen) {
//
//            $birth_year = $citizen->birth_date;
//            $birth_year = (int) explode('-', $birth_year)[0];
//            $current = (int) date('Y');
//            $age = (int) $current - $birth_year;
//
//            if($type && $type == 'woman') {
//                $gender = $citizen->gender;
//                if($gender == 1) {
//                    return response()->errorJson('Фуқаро жинси эркак бўлганлиги сабабли, аёллар дафтарига киритиб бўлмайди!', 400);
//                }
//
//                if($age < 18) {
//                    return response()->errorJson('Фуқаро 18 ёшдан кичик бўлганлиги сабабли, аёллар дафтарига киритиб бўлмайди!', 400);
//                }
//
//                if($age > 55) {
//                    return response()->errorJson('Фуқаро 55 ёшдан катта бўлганлиги сабабли, аёллар дафтарига киритиб бўлмайди!', 400);
//                }
//            }
//
//            if($type && $type == 'young') {
//                if($birth_year) {
//
//                    if($age > 30) {
//                        return response()->errorJson('Фуқаро 30 ёшдан катта бўлганлиги сабабли, ёшлар дафтарига киритиб бўлмайди!', 400);
//                    }
//                }
//            }
//            return response()->successJson($citizen);
//        } else {
//            return response()->errorJson('Фуқаро топилмади!', 404);
//        }
//    }
//
//    public function passport($request)
//    {
//        $type = \request('type', 'young');
//        $user = $this->repository->guard()->user();
//        $passports = ['AA0215962'];
//
//        if(($user->id == 262) && !in_array($request->passport, $passports)) {
//            return ['msg' => 'Маълумот топилмади', 'status' => 404];
//        }
//        $result = [];
//        if($request->birth_date){
//            $data = $this->resourceRepo->getMvdPassportData($request->passport, $request->birth_date);
//            if (!isset($data['result']['pPinpp'])) {
//                $error = isset($data['error']) ? $data['error'] : [];
//                return ['msg' => 'Маълумот топилмади', 'status' => 404];
//            } else {
//                $result = ['citizen' => $data['result']];
//            }
//        } else {
//            $data = $this->resourceRepo->getPassportData($request->passport);
//            if (isset($data['result'])) {
//
//                $tin = $data['result']['tin'] ?? null;
//
//                if (isset($tin)) {
//                    $data['result']['tin'] = $tin;
//
//                    $pin = $this->resourceRepo->getPin($tin);
//
//                    if (!is_null($pin)) {
//                        $data['result']['pin'] = $pin;
//                        $result = ['citizen' => $data['result']];
//                    } else {
//                        return ['msg' => 'Pin not found', 'status' => 404];
//                    }
//                } else {
//                    return ['msg' => 'Tin not found', 'status' => 404];
//                }
//
//            } else {
//                $error = isset($data['error']) ? $data['error'] : [];
//                return ['msg' => 'Маълумот топилмади', 'status' => 404, 'error' => $error];
//            }
//        }
//
//        if(isset($data['result']) && !empty($data['result'])) {
//
//            $birth_year = $data['result']['date_birth'] ?? $data['result']['pDateBirth'];
//            $birth_year = (int) explode('.', $birth_year)[2];
//            $current = (int) date('Y');
//            $age = (int) $current - $birth_year;
//
//            $check = $this->repository->getQuery()->where('passport', $request->passport)->first();
//
//            if($check) {
//                return ['msg' => 'Ушбу фуқаро аввал рўйхатга олинган!', 'status' => 409, 'code' => 'db'];
//            }
//
//            if($type && $type == 'woman') {
//                $gender = $data['result']['gender'] ?? $data['result']['pSex'];
//                if($gender == 1) {
//                    return ['msg' => 'Фуқаро жинси эркак бўлганлиги сабабли, аёллар дафтарига киритиб бўлмайди!', 'status' => 409, 'code' => 'db'];
//                }
//
//                if($age < 18) {
//                    return ['msg' => 'Фуқаро 18 ёшдан кичик бўлганлиги сабабли, аёллар дафтарига киритиб бўлмайди!', 'status' => 409, 'code' => 'db'];
//                }
//
//                if($age > 55) {
//                    return ['msg' => 'Фуқаро 55 ёшдан катта бўлганлиги сабабли, аёллар дафтарига киритиб бўлмайди!', 'status' => 409, 'code' => 'db'];
//                }
//            }
//
//            if($type && $type == 'young') {
//                if($birth_year) {
//
//                    if($age > 30) {
//                        return ['msg' => 'Фуқаро 30 ёшдан катта бўлганлиги сабабли, ёшлар дафтарига киритиб бўлмайди!', 'status' => 409, 'code' => 'db'];
//                    }
//                }
//            }
//            return ['status' => 200, 'citizen' => $result];
//        }
//    }
//
//    public function update($request, $id)
//    {
//        $msg = "";
//        $validator = $this->repository->toValidate($request->all());
//
//        if (!$validator->fails()) {
//            if($this->repository->getQuery()->where('id', '!=', $id)->where('phone', $request->phone)->first()) {
//                return ['msg' => 'Ушбу телефон рақами аввал киритилган!', 'status' => 409];
//            }
//            if(!$this->repository->checkCitizen($request->passport, $id)) {
//                $citizen = $this->repository->update($request, $id);
//                return  ['status' => 200, 'citizen' => $citizen];
//            } else {
//                return ['msg' => 'Bu ma\'lumotlar bazada mavjud', 'status' => 409];
//            }
//        } else {
//            $errors = $validator->failed();
//            if(empty($errors)) {
//                $msg = "Соҳалар нотўғри киритилди";
//            }
//            return ['msg' => $msg, 'status' => 422, 'error' => $errors];
//        }
//    }
//
//
//    public function show($citizen)
//    {
//        $citizen->region;
//        $citizen->city;
//        $citizen->add_field;
//        $citizen->field;
//        $citizen->other_social;
//        $citizen->family_status;
//        $citizen->place_status;
//        $citizen->reason;
//        $citizen->makhalla;
////        $citizen->citizen_status;
//        $citizen->student;
//        $citizen->university;
//        $citizen->retiree;
//        $citizen->school_graduate;
//        $citizen->self_employment;
//        $citizen->migrant;
//        $citizen->seperated_land;
////        $citizen->with('citizen_status');
//        if($citizen->credited) {
//            $citizen->credited->bank;
//        }
//
//        if($citizen->complaints) {
//            $citizen->complaints;
//            $citizen->complaints->complaintType;
//            $citizen->complaints->complaintDenyReasons;
//        }
//
//        $citizen->employment;
//
//        $position = null;
//        try {
//            if($citizen->is_employer && $citizen->citizen_status_id != 8) {
//                $position_data = $this->resourceRepo->getFulldata($citizen->pin);
//                if(!empty($position_data))
//                    $position = $position_data['result'];
//            }
//        } catch(\Exception $exception) {
//
//        }
//        $citizen->position = $position;
//        return $citizen;
//    }
//
//    public function getSeparatedLandCitizen(Request $request)
//    {
//        $condition = [];
//
//        $citizens = app(Pipeline::class)
//            ->send($this->repository->getQuery())
//            ->through([
//                \App\QueryFilters\Pin::class,
//                \App\QueryFilters\Name::class,
//                \App\QueryFilters\Passport::class,
//                \App\QueryFilters\Region::class,
//                \App\QueryFilters\City::class,
//                \App\QueryFilters\Gender::class,
//                \App\QueryFilters\Makhalla::class,
//                \App\QueryFilters\LivingPlace::class,
//            ])
//            ->thenReturn()
//            ->with('region:id,name_cyrl')
//            ->with('seperated_land')
//            ->with('makhalla')
//            ->with('city');
//
//        $citizens = $citizens->select([
//            'citizens.id',
//            'citizens.firstname',
//            'citizens.surname',
//            'citizens.patronymic',
//            'citizens.pin',
//            'citizens.passport',
//            'citizens.region_id',
//            'citizens.city_id',
//            'citizens.living_place',
//            'citizens.makhalla_id',
//            'citizens.birth_date',
//            'citizens.gender'
//        ])->where($condition)
//            ->leftJoin('separated_credits', function($join) {
//                $join->on('separated_credits.pin', '=', 'citizens.pin');
//            });
//
//        if ($request->get('count')) {
//            return $citizens->count();
//        }
//
//        return $citizens->paginate($request->get('limit', 50));
//    }

}
