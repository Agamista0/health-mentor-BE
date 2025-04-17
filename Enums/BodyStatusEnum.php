<?php
  
namespace App\Enums;
 
enum BodyStatusEnum:string {
    case good = 'GOOD';
    case possible_risk = 'POSSIBLE_RISK';
    case risk = 'RISK';
    case empty = 'EMPTY';
}