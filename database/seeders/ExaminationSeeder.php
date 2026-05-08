<?php

namespace Database\Seeders;

use App\Models\ExaminationItem;
use App\Models\ExaminationType;
use Illuminate\Database\Seeder;

class ExaminationSeeder extends Seeder
{
    public function run(): void
    {
        $examinations = [
            'فحوصات الدم العامة' => [
                'Complete Blood Count (CBC)',
                'Hemoglobin (Hb)',
                'Hematocrit (HCT)',
                'White Blood Cell Count (WBC)',
                'Platelet Count (PLT)',
                'Erythrocyte Sedimentation Rate (ESR)',
                'Blood Group and Rh Factor',
                'Reticulocyte Count',
            ],
            'فحوصات السكري' => [
                'Fasting Blood Sugar (FBS)',
                'Random Blood Sugar (RBS)',
                'HbA1c (Glycated Hemoglobin)',
                'Oral Glucose Tolerance Test (OGTT)',
                'Fasting Insulin',
                'C-Peptide',
                'Urine Microalbumin',
            ],
            'فحوصات وظائف الكبد' => [
                'Alanine Aminotransferase (ALT)',
                'Aspartate Aminotransferase (AST)',
                'Alkaline Phosphatase (ALP)',
                'Gamma-Glutamyl Transferase (GGT)',
                'Total Bilirubin',
                'Direct Bilirubin',
                'Albumin',
                'Total Protein',
                'Prothrombin Time (PT/INR)',
            ],
            'فحوصات وظائف الكلى' => [
                'Serum Creatinine',
                'Blood Urea Nitrogen (BUN)',
                'Uric Acid',
                'Estimated Glomerular Filtration Rate (eGFR)',
                'Sodium (Na)',
                'Potassium (K)',
                'Chloride (Cl)',
                'Calcium (Ca)',
                'Phosphorus (P)',
                'Urine Protein/Creatinine Ratio',
            ],
            'فحوصات الدهون والقلب' => [
                'Total Cholesterol',
                'Triglycerides',
                'HDL Cholesterol',
                'LDL Cholesterol',
                'VLDL Cholesterol',
                'Apolipoprotein A1',
                'Apolipoprotein B',
                'Lipoprotein(a)',
                'High-Sensitivity CRP (hs-CRP)',
                'Troponin I',
                'Creatine Kinase-MB (CK-MB)',
            ],
            'فحوصات الغدة الدرقية' => [
                'Thyroid Stimulating Hormone (TSH)',
                'Free T4',
                'Free T3',
                'Total T4',
                'Total T3',
                'Anti-Thyroid Peroxidase Antibody (Anti-TPO)',
                'Anti-Thyroglobulin Antibody',
                'Thyroglobulin',
            ],
            'فحوصات الفيتامينات والمعادن' => [
                'Vitamin D (25-OH)',
                'Vitamin B12',
                'Folate',
                'Ferritin',
                'Serum Iron',
                'Total Iron Binding Capacity (TIBC)',
                'Transferrin Saturation',
                'Magnesium',
                'Zinc',
            ],
            'فحوصات الهرمونات' => [
                'Prolactin',
                'Follicle Stimulating Hormone (FSH)',
                'Luteinizing Hormone (LH)',
                'Estradiol (E2)',
                'Progesterone',
                'Total Testosterone',
                'Free Testosterone',
                'Dehydroepiandrosterone Sulfate (DHEA-S)',
                'Cortisol',
                'Beta-hCG',
            ],
            'فحوصات المناعة والحساسية' => [
                'C-Reactive Protein (CRP)',
                'Rheumatoid Factor (RF)',
                'Anti-Cyclic Citrullinated Peptide (Anti-CCP)',
                'Antinuclear Antibody (ANA)',
                'Complement C3',
                'Complement C4',
                'Total IgE',
                'Allergy Food Panel',
                'Allergy Inhalant Panel',
            ],
            'فحوصات العدوى والفيروسات' => [
                'Hepatitis B Surface Antigen (HBsAg)',
                'Hepatitis B Surface Antibody (Anti-HBs)',
                'Hepatitis C Antibody (Anti-HCV)',
                'HIV Ag/Ab Combo',
                'COVID-19 PCR',
                'Influenza A/B Antigen',
                'Helicobacter pylori Antigen',
                'Typhoid Test',
                'Blood Culture',
                'Urine Culture',
            ],
            'فحوصات البول والبراز' => [
                'Urinalysis',
                'Urine Culture and Sensitivity',
                'Urine Pregnancy Test',
                'Stool Analysis',
                'Stool Culture',
                'Occult Blood in Stool',
                'Stool H. pylori Antigen',
                'Stool Ova and Parasites',
            ],
            'فحوصات التخثر والسيولة' => [
                'Prothrombin Time (PT)',
                'International Normalized Ratio (INR)',
                'Activated Partial Thromboplastin Time (aPTT)',
                'D-Dimer',
                'Fibrinogen',
                'Bleeding Time',
                'Clotting Time',
            ],
        ];

        foreach ($examinations as $typeName => $items) {
            $type = ExaminationType::updateOrCreate(
                ['name' => $typeName],
                ['name' => $typeName]
            );

            foreach ($items as $itemName) {
                ExaminationItem::updateOrCreate(
                    [
                        'name' => $itemName,
                        'examination_type_id' => $type->id,
                    ],
                    ['name' => $itemName]
                );
            }
        }
    }
}
