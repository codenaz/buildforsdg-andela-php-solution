<?php

function covid19ImpactEstimator($data)
{
  var_dump($data);
  $impact = getStats($data);

  $severeImpact = getStats($data, 'severe');

  return ['data' => $data, 'impact' => $impact, 'severeImpact' => $severeImpact];
}

function getStats($data, $case = 'regular')
{
  $multiplier = $case === 'severe' ? 50 : 10;

  $currentlyInfected = $data['reportedCases'] * $multiplier;

  $durationInDays = getDurationInDays($data['timeToElapse'], $data['periodType']);

  $infectionsByRequestedTime = (int) $currentlyInfected * (2 ** floor($durationInDays / 3));

  $severeCasesByRequestedTime = 0.15 * $infectionsByRequestedTime;

  $hospitalBedsByRequestedTime =  (0.35 * $data['totalHospitalBeds']) - $severeCasesByRequestedTime;

  $casesForICUByRequestedTime = 0.05 * $infectionsByRequestedTime;

  $casesForVentilatorsByRequestedTime = 0.02 * $infectionsByRequestedTime;

  $region = $data['region'];

  $dollarsInFlight = $infectionsByRequestedTime * $durationInDays * $region['avgDailyIncomeInUSD'] * $region['avgDailyIncomePopulation'];


  return [
    'currentlyInfected' => $currentlyInfected,
    'infectionsByRequestedTime' => $infectionsByRequestedTime,
    'severeCasesByRequestedTime' => $severeCasesByRequestedTime,
    'hospitalBedsByRequestedTime' => $hospitalBedsByRequestedTime,
    'casesForICUByRequestedTime' => $casesForICUByRequestedTime,
    'casesForVentilatorsByRequestedTime' => $casesForVentilatorsByRequestedTime,
    'dollarsInFlight' => number_format($dollarsInFlight, 2, '.', '')
  ];
}

function getDurationInDays($duration, $periodType)
{
  switch ($periodType) {
    case 'weeks':
      $computedDuration = $duration * 7;
      break;
    case 'months':
      $computedDuration = $duration * 30;
      break;
    default:
      $computedDuration = $duration;
  }
  return $computedDuration;
}
