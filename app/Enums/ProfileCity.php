<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum ProfileCity: string
{
    use EnumToArray;

    use EnumToArray;
    case Al_Quneitra = 'القنيطرة';
    case Aleppo = 'حلب';
    case Tartus = 'طرطوس' ;
    case Latakia = 'اللاذقية' ;
    case Idlib = 'ادلب';
    case Homs = 'حمص';
    case Hama = 'حماة';
    case Damascus = 'دمشق';
    case Damascus_Countryside = 'ريف دمشق';
    case Deir_Al_Zor = 'دير الزور';
    case Daraa = 'درعا';
    case Al_Suwayda = 'السويداء';
    case AL_Raqqa = 'الرقة';
    case Al_Qamishli = 'القامشلي';
    case Al_Hasakah = 'الحسكة';
}
